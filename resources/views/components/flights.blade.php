{{-- @dd($flightData) --}}
{{-- HOTEL BOOKING PAX COUNT RULES
    6 rooms per request
    9 adults per room
    4 children per room (age 1–17)
--}}
@php
    use Carbon\Carbon;
    use App\Helpers\HelperFunctions;
    // Temp
    use Illuminate\Support\Facades\Cache;

    // flight_data_rtn
    // flight_data_ow
    // $flightData = Cache::remember('flight_data_rtn', 6600, function () use ($flightData) {
    //     return $flightData; // first response will be cached
    // });
    // Temp

@endphp
<style>
    .flight-card {
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 20px;
        margin: 20px auto;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .airline-logo {
        width: 60px;
        height: auto;
    }

    .price-btn,
    .price-btn-rtn {
        background-color: #004080;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: bold;
    }

    .details-section {
        display: none;
        border-top: 1px dashed #ccc;
        margin-top: 15px;
        padding-top: 15px;
        font-size: 0.9rem;
    }

    .connected {
        font-size: 1em;
        color: #127f9f;
        cursor: pointer;
        text-decoration: underline;
        font-weight: 600;
    }

    .durationBadge {
        border: 1px solid #127f9f;
        background: #2fbbe530;
        padding: 2px 4px;
        border-radius: 5px;
        font-size: 0.8em !important;
    }

    .pia-bundle-item {
        border: 1px solid #ddd;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        background: #fafafa;
    }

    .bundle-header {
        margin-bottom: 12px;
    }

    .bundle-header h4 {
        margin: 0 0 4px;
    }

    .baggage-summary {
        font-size: 0.9rem;
        color: #555;
    }

    .option-card {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
        background: #fff;
    }

    .option-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .option-header h5 {
        margin: 0;
        font-size: 1rem;
    }

    .option-price {
        font-weight: 600;
        color: #008000;
    }

    .fare-list {
        padding-left: 20px;
        margin: 5px 0;
    }

    .service-list {
        margin-top: 5px;
    }

    .service-badge {
        background: #e9ecef;
        padding: 3px 8px;
        border-radius: 4px;
        margin-right: 4px;
        font-size: 0.8rem;
    }

    .bundle-footer {
        margin-top: 12px;
        text-align: right;
    }

    .df-items.plane {
        display: flex;
        align-items: end;
        justify-content: space-between;
    }

    .df-items.plane h1 {
        font-size: 30px;
    }

    .df-items.plane p {
        font-size: 18px;
        font-weight: 400;
    }

    .timesHeading {
        display: flex;
        justify-content: space-around;
    }

    .flight-duration {
        margin: 11px 0;
    }

    .price-btn,
    .price-btn-rtn {
        padding: 8px 14px;
        font-size: 13px;
        width: 100%;
    }

    .text-muted.small.roundtrip {
        text-align: center;
        font-weight: 600;
        color: #000;
    }

    .fare-scroll {
        display: flex;
        overflow-x: auto;
        gap: 1rem;
        padding-bottom: 1rem;
        scroll-snap-type: x mandatory;
    }

    .fare-scroll::-webkit-scrollbar {
        height: 8px;
    }

    .fare-scroll::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }

    .fare-scroll>.card {
        flex: 0 0 auto;
        scroll-snap-align: start;
    }

    .bundle-section {
        display: none;
    }

    .bundle-loader {
        padding: 20px;
        text-align: center;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 4px solid #e0e0e0;
        border-top-color: #007bff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
{{-- @dd($flightData) --}}
@if (!empty($flightData))
    @php
        $isReturn = $flightData['return_count'] > 0;
        $bundles = $flightData['bundles'];

    @endphp
    @forelse ($flightData['flights'] as $key => $segments)
        @php
            // $departure = $key === 0 ? $flightData['departure'] : $flightData['arrival'];
            // $arrival = $key === 0 ? $flightData['arrival'] : $flightData['departure'];
        @endphp
        <div class="departure_names" id="{{ $key === 0 ? 'departure-section' : 'return-section' }}"
            style="display:{{ $key === 0 ? 'block' : 'none' }};">
            {{-- <div class="df-items plane">
                <h1>{{ $key === 0 ? 'Departure' : 'Return' }} Flights</h1>
                <p class="small font-italic">
                    {{ $departure['airport'] }} ({{ $departure['code'] }}) →
                    {{ $arrival['airport'] }} ({{ $arrival['code'] }})
                </p>
            </div> --}}
            {{-- Flights :) --}}
            @forelse ($segments as $flight)
                {{-- @dd($flightData, $flight) --}}
                @php
                    $logo = strtolower($flight['carrier']);
                    $flightDep = $flight['departure'];
                    $flightArr = $flight['arrival'];
                    $firstSegment = $flight['segments'][0] ?? $flight['segments'];
                    $stopCount = count($flight['segments']) - 1;
                @endphp
                <div class="flight-card">
                    <!-- Airline Info -->
                    <div class="row align-items-center text-center text-md-start">
                        <div class="col-12 col-md-2 mb-3 mb-md-0 d-flex flex-column align-items-center">
                            <img src="{{ asset('assets/images/logos/' . $logo . '.png') }}" alt="{{ strtoupper($logo) }}"
                                class="airline-logo mb-1">
                            <div><strong>{{ $flight['carrier'] ?? '' }}</strong></div>
                            <div class="text-muted small flight-nums">
                                <p>{{ $firstSegment['carrier'] }} ({{ $firstSegment['flight_number'] }})</p>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <!-- Time Info -->
                            <div class="timesHeading">
                                <div>
                                    <h2> <strong>{{ $flightDep['time'] ?? '' }}</strong></h2>
                                </div>
                                <div class="flight-duration">{{ $flight['duration'] ?? '' }}</div>
                                <div>
                                    <h2><strong>{{ $flightArr['time'] ?? '' }}</strong></h2>
                                </div>
                            </div>
                            <div class="my-3 flight-names-dec text-center">
                                <p>{{ $flightDep['airport'] }} ({{ $flightDep['code'] }}) -
                                    @if ($flight['isConnected'])
                                        <span class="connected">{{ $stopCount }}
                                            {{ $stopCount > 1 ? 'Stops' : 'Stop' }}</span>
                                    @else
                                        Nonstop
                                    @endif
                                    - {{ $flightArr['airport'] }} ({{ $flightArr['code'] }})
                                </p>
                            </div>
                            {{-- <div class="text-muted small kgs-total">🧳 Total: 20kg &nbsp;&nbsp; 🍴 Meal</div> --}}
                        </div>

                        <!-- Price Info -->
                        <div class="col-12 col-md-2 text-md-end">
                            <button class="{{ $key === 0 ? 'price-btn' : 'price-btn-rtn' }} mb-2"
                                data-flight="{{ json_encode($flight) }}">
                                @php
                                    $isPiaReturn = ($flight['carrier'] ?? '') === 'pia' && $key === 1;
                                @endphp
                                {{ $flight['code'] ?? 'PKR' }} {{ $isPiaReturn ? '0' : ($flight['price'] ?? 0) }}
                            </button>
                            <div class="text-muted small roundtrip">
                                {{ $flightData['return_count'] === 0 ? 'One Way' : 'Round Trip' }}</div>
                        </div>
                    </div>
                    <!-- Connected Flight Details -->
                    <div class="details-section">
                        <div class=" mb-3">
                            <span
                                class="durationBadge text-dark">{{ Carbon::parse($flight['arrival']['datetime'])->format('l d, F') }}</span>
                        </div>
                        @forelse ($flight['segments'] as $index => $segment)
                            <div class="card mb-3 shadow-sm">
                                <div class="card-body d-flex flex-wrap align-items-center">
                                    <!-- Airline -->
                                    <div class="col-6 col-md-2 text-center mb-3 mb-md-0">
                                        <img src="{{ asset('assets/images/logos/' . $logo . '.png') }}"
                                            alt="{{ strtoupper($logo) }}" class="airline-logo"
                                            style="max-height:40px; max-width:40px;">
                                    </div>

                                    <!-- Departure -->
                                    <div class="col-6 col-md-2 mb-3 mb-md-0">
                                        <p class="fw-bold mb-1">
                                            {{ Carbon::parse($segment['departure']['datetime'])->format('h:i A') }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $segment['departure']['airport'] }}
                                            ({{ $segment['departure']['code'] }})
                                        </small>
                                    </div>

                                    <!-- Duration -->
                                    <div class="col-6 col-md-2 mb-3 mb-md-0 text-center">
                                        <div class="flight-duration">
                                            {{ str_replace(['PT', 'H', 'M'], ['', 'h ', 'm'], $segment['duration']) }}
                                        </div>
                                    </div>

                                    <!-- Arrival -->
                                    <div class="col-6 col-md-2 mb-3 mb-md-0">
                                        <p class="fw-bold mb-1">
                                            {{ Carbon::parse($segment['arrival']['datetime'])->format('h:i A') }}
                                        </p>
                                        <small class="text-muted">
                                            {{ $segment['arrival']['airport'] }} ({{ $segment['arrival']['code'] }})
                                        </small>
                                    </div>

                                    <!-- Flight No -->
                                    <div class="col-6 col-md-2 mb-3 mb-md-0 text-center">
                                        <p class="fw-bold mb-1">Flight No</p>
                                        <span
                                            class="badge bg-light text-dark">{{ $segment['carrier'] }}-{{ $segment['flight_number'] }}</span>
                                    </div>

                                    <!-- Cabin Class -->
                                    <div class="col-6 col-md-2 text-md-end">
                                        <p class="fw-bold mb-1">Cabin Class</p>
                                        <span class="badge bg-secondary text-light">{{ $flight['cabinClass'] }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Show layover only if this is not the last segment --}}
                            @if (isset($flight['segments'][$index + 1]))
                                @php
                                    $arrival = Carbon::parse($segment['arrival']['datetime']);
                                    $nextDeparture = Carbon::parse(
                                        $flight['segments'][$index + 1]['departure']['datetime'],
                                    );
                                    $layover = $arrival->diff($nextDeparture);
                                    // dd($segment, $flight, $segment['arrival']['datetime'], $flight['segments'][$index+1]['departure']['datetime'], $layover);
                                @endphp

                                @if ($layover->h > 0 || $layover->i > 0)
                                    <div class="text-center mb-3">
                                        <span class="badge bg-warning text-dark">
                                            {{ $layover->h ? $layover->h . 'h ' : '' }}{{ $layover->i ? $layover->i . 'm' : '' }}
                                            layover in {{ $segment['arrival']['airport'] }}
                                        </span>
                                    </div>
                                @endif
                            @endif
                        @empty
                            <p class="text-center text-muted">No flights available.</p>
                        @endforelse
                    </div>
                    <!-- Bundle Details -->
                    <div class="bundle-section my-4">
                        <h5 class="mb-3">Select a fare option</h5>
                        <!-- Horizontal Scroll Wrapper -->
                        <div class="fare-scroll bundle-loop">
                            <div class="bundle-loader w-100">
                                <div class="spinner"></div>
                                <p class="small text-muted mt-2">Loading bundles...</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-muted">No flights available.</p>
            @endforelse
        </div>
    @empty
        <p class="text-center text-muted">No flights available.</p>
    @endforelse
@endif
<script>
    $(document).ready(function() {
        let paxCount = @json($paxCount);
        let isReturn = @json($isReturn);
        let bundles = @json($bundles);
        const bookingPage = "{{ route('flightBooking') }}";
        let selectedDepartureBundlePrice = 0; // Track selected departure bundle price for PIA
        let selectedTotalPricePkr = 0; // Track total_price_pkr from selected combination
        // console.log(bundles);
        let extras = @json($flightData['extras']);
        let departureFlight, returnFlight, returnFlightRaw, selectedCarrier;
        let responseId, firstBundleId, offerIdsDep, secondBundleId, offerIdsRtn;
        let firstFlight, firstConnectedFlight, returnConnectedFlight;
        let segments, flightTotalFare, rtnSelectedFlight, airline, depSelectedFlight;
        // let firstSegments, secondSegments;
        $('.connected').off('click').on('click', function() {
            $(this).closest(".flight-card").find(".details-section").slideToggle();
        });

        $(".price-btn").click(function() {
            departureFlight = $(this).data('flight');
            selectedCarrier = departureFlight.carrier;
            if (selectedCarrier === 'pia') {
                // console.log('PIA Price button clicked, departureFlight:', departureFlight);
                // console.log('departureFlight keys:', Object.keys(departureFlight));
                // console.log('Looking for journey_id in flightRaw:', departureFlight.flightRaw);
                renderPiaBundles(this, false);
            }
            if (selectedCarrier === 'flyJinnah') {
                depSelectedFlight = departureFlight.flightRaw;
                firstFlight = getFlightData(depSelectedFlight.flightSegments[0]);
                firstConnectedFlight = getFlightData(depSelectedFlight.flightSegments[1] || null);
                getFlightBundle(firstFlight, firstConnectedFlight, this, false, null, null);
            }
            if (selectedCarrier === 'emirates') {
                renderEmirateBundles(departureFlight.bundles || [], this, false);
            }
            if (selectedCarrier === 'airblue') {
                renderAirblueBundles(departureFlight.bundles || [], this, false);
            }
            if (!isReturn) return;

            let matchingFlights = 0;

            $("#return-section .flight-card").hide();

            $("#return-section .flight-card").each(function() {
                let rtnFlight = $(this).find("button").data("flight");
                if (rtnFlight.carrier === selectedCarrier) {
                    $(this).show();
                    matchingFlights++;
                }
            });

            if (matchingFlights === 0) {
                _alert(("No return flights available for " + selectedCarrier), 'warning');
                return;
            }

            // if (selectedCarrier === 'pia') {
            //     $('#departure-text').removeClass('active');
            //     $('#return-text').addClass('active');
            //     $("#departure-section").slideUp(500, function() {
            //         $("#return-section").fadeIn(500);
            //     });
            // }
        });

        $(".price-btn-rtn").click(function() {
            returnFlight = $(this).data('flight');
            returnFlightRaw = returnFlight;
            if (selectedCarrier === 'emirates') {
                renderEmirateBundles(returnFlight.bundles || [], this, true);
            } else if (selectedCarrier === 'flyJinnah') {
                rtnSelectedFlight = returnFlightRaw.flightRaw;
                let newReturnFlight = getFlightData(rtnSelectedFlight.flightSegments[0]);
                returnConnectedFlight = getFlightData(rtnSelectedFlight.flightSegments[1] || null);
                getFlightBundle(firstFlight, firstConnectedFlight, this, true, newReturnFlight,
                    returnConnectedFlight);
            } else if (selectedCarrier === "airblue") {
                renderAirblueBundles(returnFlight.bundles || [], this, true);
            } else if (selectedCarrier === "pia") {
                renderPiaBundles(this, true);
            } else {
                alert('missing flight');
            }
        });
        const renderEmirateBundles = (data, el, isReturn) => {
            responseId = extras.emirates.responseId ?? '';
            const $flightCard = $(el).closest(".flight-card");
            const $bundleSection = $flightCard.find(".bundle-section");
            const $bundleLoop = $flightCard.find(".bundle-loop");
            $bundleSection.slideToggle();

            // $bundleLoop.html(`
            //     <div class="bundle-loader w-100">
            //         <div class="spinner"></div>
            //         <p class="small text-muted mt-2">Loading bundles...</p>
            //     </div>
            // `);

            if (!data || data.length === 0) {
                setTimeout(() => {
                    $bundleLoop.html(
                        `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No bundles available for Emirates</div>`
                    );
                }, 400);
                return;
            }

            const normalizedData = Array.isArray(data) ? data : [data];
            setTimeout(() => {
                const cardsHtml = normalizedData.map(row => {
                    const shortTexts = (row.priceClass?.Descriptions?.Description || [])
                        .filter(item => item?.Text && Object.keys(item).length === 1)
                        .map(item => `<li>${item.Text.value}</li>`)
                        .join('');

                    const name = row.priceClass?.Name?.value ?? 'N/A';
                    const code = row.totalPrice?.code ?? 'PKR';
                    const amount = formatCurrency(Math.round(row.totalPrice?.amount || 0));
                    const offerId = row.offerID?.OfferID ?? '';

                    return `
                        <div class="card h-100 shadow-sm mx-2">
                            <div class="card-header bg-light fw-bold">
                                ${name}
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">${shortTexts}</ul>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <button
                                    class="btn btn-primary w-100 fw-bold bookBtn"
                                    data-airline="emirate"
                                    data-is-return="${isReturn}"
                                    data-bundle-id="${encodeURIComponent(JSON.stringify(row['offerID']))}"
                                    data-response-id="${responseId}"
                                    data-offer-ids="${encodeURIComponent(JSON.stringify(getOfferIds(row['offerItem'])))}"
                                    >
                                    ${code} ${amount}
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                $bundleLoop.html(`
                    <div class="fare-scroll d-flex overflow-auto pb-3">${cardsHtml}</div>
                `);
            }, 500);
        }
        const formatFlight = (flight) => {
            if (!flight) return {};

            const carrier = flight.carrier || '';
            let stopCount = flight.segments ? flight.segments.length - 1 : 0;

            return {
                logo: carrier ? carrier.toLowerCase() : 'default',
                carrier: carrier,
                depTime: flight.departure?.time || '',
                arrTime: flight.arrival?.time || '',
                origCode: flight.departure?.code || '',
                destCode: flight.arrival?.code || '',
                timeDiff: flight.duration || '',
                stops: stopCount > 0 ? `${stopCount} ${stopCount > 1 ? 'Stops' : 'Stop'}` : 'Nonstop',
                price: flight.price || 0,
                priceCode: flight.code || ''
            };
        };
        const flightHtml = (depInfo, rtnInfo) => {
            const renderFlight = (info) => `
            <li>
                <div class="sugge-tab sugge-tab-time2">
                    <div class="flex1">
                        <div class="emri">
                            <img class="${rtnInfo ? 'w-75' : 'w-50'} p-2" src="/assets/images/logos/modal/${info.logo}.png" alt="${info.carrier}">
                        </div>
                        <div class="der-time">
                            <ul>
                                <li><h2>${info.depTime}</h2></li>
                                <li><div class="stays"><p>${info.timeDiff}</p></div></li>
                                <li><h2>${info.arrTime}</h2></li>
                            </ul>
                            <div class="citys">
                                <div class="cit">
                                    <ul>
                                        <li><p>${info.origCode}</p></li>
                                        <li><p>-</p></li>
                                        <li><p>${info.stops}</p></li>
                                        <li>-</li>
                                        <li><p>${info.destCode}</p></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pritik">
                        <a class="bg-info btn text-light" role="button">
                            ${info.priceCode} ${info.price}
                        </a>
                    </div>
                </div>
            </li>`;

            return `<ul>${renderFlight(depInfo)}${rtnInfo ? renderFlight(rtnInfo) : ''}</ul>`;
        };

        const getOfferIds = data =>
            (Array.isArray(data) ? data : data ? [data] : []).map(item => ({
                id: item?.id || null,
                PassengerRef: item?.services?.[0]?.passengerRefs || null
            }));
        // AJAX to get bundles
        const getFlightBundle = (firstFlight, firstConnectedFlight, element, direction, returnFlight,
            returnConnectedFlight) => {
            // console.log(firstFlight, firstConnectedFlight, direction, returnFlight, returnConnectedFlight);
            const $flightCard = $(element).closest(".flight-card");
            const $bundleSection = $flightCard.find(".bundle-section");
            const $bundleLoop = $flightCard.find(".bundle-loop");
            const $loader = $flightCard.find(".bundle-loader");

            if ($flightCard.data("loading-bundles")) return;
            $flightCard.data("loading-bundles", true);

            if ($loader.length === 0) {
                $bundleSection.slideToggle();
                $flightCard.data("loading-bundles", false);
                return;
            }
            $.ajax({
                type: "POST",
                url: "{{ route('get_bundles') }}",
                data: {
                    firstFlight,
                    firstConnectedFlight,
                    returnFlight,
                    returnConnectedFlight,

                    // firstFlight:flight, connectedFlight,
                    paxCount,
                    isReturn,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: () => $bundleSection.slideToggle(),
                success: (res) => {
                    // segments = getSegment(res.originDestinationOptions.FlightSegment) || res
                    //     .originDestinationOptions.map(item => getSegment(item.FlightSegment));
                    // flightTotalFare = res['prices']['ItinTotalFare'] ?? null;

                    if (res.error) {
                        $bundleLoop.html(
                            `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No bundles available</div>`
                        );
                        return;
                    }
                    if (!res.bundles || res.bundles === "Not found") {
                        showBasicOnly(element, res, direction, firstFlight,
                            firstConnectedFlight, returnFlight, returnConnectedFlight);
                        return;
                    }
                    renderBundles(res || [], element, direction, firstFlight,
                        firstConnectedFlight, returnFlight, returnConnectedFlight);
                },
                error: (xhr, status, error) => {
                    console.error('Error:', error)
                    $bundleLoop.html(
                        `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No bundles available</div>`
                    );
                },
                complete: () => {
                    $flightCard.data("loading-bundles", false);
                }
            });
        };

        function showBasicOnly(el, data, isReturn, firstFlight, firstConnectedFlight, returnFlight,
            returnConnectedFlight) {
            const $flightCard = $(el).closest(".flight-card");
            const $bundleLoop = $flightCard.find(".bundle-loop");

            const segments = getSegment(data.originDestinationOptions.FlightSegment) ||
                data.originDestinationOptions.map(item => getSegment(item.FlightSegment));

            const flightTotalFare = data?.prices?.ItinTotalFare ?? null;

            const flightData = isReturn ? {
                firstFlight,
                firstConnectedFlight,
                returnFlight,
                returnConnectedFlight
            } : {
                firstFlight,
                firstConnectedFlight
            };

            $bundleLoop.html(`
                <div class="card shadow-sm mx-2">
                    <div class="card-header bg-light fw-bold">
                        Basic
                    </div>
                    <div class="card-body">
                        <span class="fw-bold">Included</span>
                        <ul class="list-unstyled small">
                            <li>Check-in: 10 Kg</li>
                            <li>Checked Baggage (Baggage Rate)</li>
                            <li>Seat</li>
                            <li>Meal</li>
                            <li>Modification (Penalties Apply)</li>
                            <li>Cancellation (Penalties Apply)</li>
                        </ul>
                    </div>
                    <div class="card-footer text-center bg-white">
                        <button class="btn btn-primary w-100 fw-bold bookBtn"
                            data-airline="flyjinnah"
                            data-flight='${JSON.stringify(flightData).replace(/'/g, "&apos;")}'
                            data-segments='${JSON.stringify(segments).replace(/"/g, "&quot;")}'
                            data-flight-total-fare='${JSON.stringify(flightTotalFare).replace(/"/g, "&quot;")}'
                            data-is-return="${isReturn}"
                            data-bundle-id="basic">
                            + PKR 0.00
                        </button>
                    </div>
                </div>
            `);
        }

        const renderBundles = (data, el, isReturn, firstFlight, firstConnectedFlight, returnFlight,
            returnConnectedFlight) => {
            let useBundleId = data?.bundles?.[0]?.bundledService?.some(b => b.bunldedServiceId ==
                    firstBundleId) ?
                firstBundleId :
                null;

            // let useBundleId = data.bundles[0] ? (data.bundles[0].bundledService.some(b => b.bunldedServiceId == firstBundleId) ? firstBundleId : null) : null;
            firstBundleId = (firstBundleId === 'basic') ? 'basic' : (isReturn ? useBundleId : useBundleId);
            const bundles = isReturn ? (data?.bundles[1].bundledService || []) : (data?.bundles
                .bundledService || []);
            const bundlesArray = Array.isArray(bundles) ? bundles : (bundles ? [bundles] : []);
            const $flightCard = $(el).closest(".flight-card");
            const $bundleSection = $flightCard.find(".bundle-section");
            const $bundleLoop = $flightCard.find(".bundle-loop");
            const segments = getSegment(data.originDestinationOptions.FlightSegment) || data
                .originDestinationOptions.map(item => getSegment(item.FlightSegment));
            const flightTotalFare = data['prices']['ItinTotalFare'] ?? null;

            const flightData = isReturn ? {
                firstFlight,
                firstConnectedFlight,
                returnFlight,
                returnConnectedFlight
            } : {
                firstFlight,
                firstConnectedFlight
            };
            if (!bundlesArray || bundlesArray.length === 0) {
                $bundleLoop.html(
                    `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No bundles available</div>`
                );
                return;
            }
            setTimeout(() => {
                const staticCard = `
                    <div class="card shadow-sm mx-2">
                        <div class="card-header bg-light fw-bold">
                            Basic
                        </div>
                        <div class="card-body">
                            <span class="fw-bold">Included</span>
                            <ul class="list-unstyled small">
                                <li>Check-in: 10 Kg</li>
                                <li>Checked Baggage (Baggage Rate)</li>
                                <li>Seat</li>
                                <li>Meal</li>
                                <li>Modification (Penalties Apply)</li>
                                <li>Cancellation (Penalties Apply)</li>
                            </ul>
                        </div>
                        <div class="card-footer text-center bg-white">
                            <button class="btn btn-primary w-100 fw-bold bookBtn"
                                data-airline="flyjinnah"
                                data-flight='${JSON.stringify(flightData).replace(/'/g, "&apos;")}'
                                data-segments='${JSON.stringify(segments).replace(/"/g, "&quot;")}'
                                data-flight-total-fare="${JSON.stringify(flightTotalFare).replace(/"/g, "&quot;")}"
                                data-is-return="${isReturn}"
                                data-bundle-id="basic">
                                + PKR 0.00
                            </button>
                        </div>
                    </div>
                `;
                const dynamicCards = bundles
                    .filter(row => row.description && String(row.description).trim() !== "")
                    .map(row => {
                        const name = row.bundledServiceName ?? "N/A";
                        const price = formatCurrency(Math.round(row.perPaxBundledFee || 0));
                        const descArr = Array.isArray(row.description) ? [] : (row
                            .description || "").split("\n");
                        const descHTML = descArr.map(d => `<li>${d}</li>`).join("");
                        return `
                            <div class="card shadow-sm mx-2">
                                <div class="card-header bg-light fw-bold">
                                    ${name}
                                </div>
                                <div class="card-body">
                                    <span class="fw-bold">Included</span>
                                    <ul class="list-unstyled small">${descHTML}</ul>
                                </div>
                                <div class="card-footer text-center bg-white">
                                    <button class="btn btn-primary w-100 fw-bold bookBtn"
                                        data-airline="flyjinnah"
                                        data-flight='${JSON.stringify(flightData).replace(/'/g, "&apos;")}'
                                        data-segments='${JSON.stringify(segments).replace(/"/g, "&quot;")}'
                                        data-flight-total-fare="${JSON.stringify(flightTotalFare).replace(/"/g, "&quot;")}}"
                                        data-is-return="${isReturn}"
                                        data-bundle-id="${row['bunldedServiceId']}">
                                        + PKR ${price}
                                    </button>
                                </div>
                            </div>
                        `;
                    })
                    .join("");
                const finalOutput = dynamicCards.trim() === "" ?
                    `<div class="alert alert-warning">No valid bundles available</div>` :
                    `
                        <div class="fare-scroll d-flex overflow-auto pb-3">
                            ${staticCard}
                            ${dynamicCards}
                        </div>
                    `;
                $bundleLoop.html(finalOutput);
            }, 300);
        };

        $(document).on('click', '.bookBtn', function() {
            airline = $(this).data('airline');
            if (['flyjinnah', 'emirate', 'airblue'].includes(airline)) {
                let isDirect = false;
                let bundleId = $(this).data('bundle-id') ?? null;
                let isReturnBundle = $(this).data('is-return');
                let offerIds = $(this).data('offer-ids') ?? null;
                let segmentsRaw = $(this).data('segments') ?? null;
                flightTotalFare = $(this).data('flight-total-fare') ?? null;
                responseId = $(this).data('response-id') ?? null;
                const flightData = $(this).data('flight') ?? null;
                if (!isReturnBundle) {
                    if (airline === 'flyjinnah') {
                        // console.log(flightData)
                        firstFlight = flightData.firstFlight;
                        firstConnectedFlight = flightData.firstConnectedFlight;
                        // console.log('firstFlight', firstFlight, firstConnectedFlight)
                    }
                    //FJ
                    segments = segmentsRaw;
                    // firstFlightTotalFare = flightTotalFare;
                    //FJ
                    firstBundleId = bundleId;
                    offerIdsDep = offerIds;
                    if (isReturn) {
                        $('#departure-text').removeClass('active');
                        $('#return-text').addClass('active');
                        $("#departure-section").slideUp(500, function() {
                            $("#return-section").fadeIn(500);
                        });
                    }
                } else {
                    // if (!firstBundleId) {
                    //     _alert('You must select the first bundle before selecting the return bundle.', 'warning');
                    //     return;
                    // }
                    //FJ
                    segments = segmentsRaw;
                    // secondFlightTotalFare = flightTotalFare;
                    //FJ
                    secondBundleId = bundleId;
                    offerIdsRtn = offerIds;
                    if (airline === 'flyjinnah') {
                        secondFlight = flightData.returnFlight;
                        secondConnectedFlight = flightData.returnConnectedFlight;
                        isDirect = false;
                    }
                }
                // console.log(firstBundleId)
                if (firstBundleId && (!isReturn || secondBundleId)) {
                    if (airline === 'flyjinnah') {
                        isDirect = firstBundleId === 'basic' && (!isReturn || secondBundleId ===
                            'basic');
                    }
                    sendBookingRequest(isDirect);
                }
            } else if (['pia'].includes(airline)) {
                const bundleName = $(this).data('bundle-name');
                const recommendedId = $(this).data('recommended-id');
                const isReturnBtn = $(this).data('is-return');
                const bundlePrice = $(this).data('bundle-price'); // Get the bundle price
                const totalPrice = $(this).data('total-price'); // Get total_price_pkr from combination
                
                if (!isReturnBtn) {
                    firstBundleId = bundleName; // Store name (e.g. ECOLIGHT)
                    responseId = recommendedId; // Store for outbound
                    selectedDepartureBundlePrice = bundlePrice; // Store departure bundle price
                    selectedTotalPricePkr = totalPrice; // Store total price
                    
                    if (isReturn) {
                        // Switch to Return View
                    $('#departure-text').removeClass('active');
                    $('#return-text').addClass('active');
                    $("#departure-section").slideUp(500, function() {
                        $("#return-section").fadeIn(500);
                    });
                    } else {
                        // One Way - Ready to book
                        sendBookingRequest(false);
                    }
                } else {
                    // Return Flight Selected
                    secondBundleId = bundleName;
                    responseId = recommendedId; // This is the recommended_offer_id for the WHOLE itinerary (Out+In)
                    selectedTotalPricePkr = totalPrice; // Update with return combination total price
                    
                    sendBookingRequest(false);
                }

            } else {
                _alert('Missing Carrier', 'warning')
            }
        });
        const getFlightData = data => {
            if (!data) return null;
            return {
                departure: data['departureDateTimeLocal'],
                arrival: data['arrivalDateTimeLocal'],
                origin: data['origin'],
                destination: data['destination'],
                flightNumber: data['flightNumber']
            };
        };
        const getSegment = data => {
            if (!data) return null;
            return {
                departure: data['@attributes']['DepartureDateTime'],
                arrival: data['@attributes']['ArrivalDateTime'],
                origin: data['ArrivalAirport']['@attributes']['LocationCode'],
                destination: data['DepartureAirport']['@attributes']['LocationCode'],
                flightNumber: data['@attributes']['FlightNumber'],
                returnFlag: data['@attributes']['returnFlag'],
                rph: data['@attributes']['RPH'],
                arrTerminal: data['ArrivalAirport']['@attributes']['Terminal'],
                depTerminal: data['DepartureAirport']['@attributes']['Terminal']
            };
        };
        const parseDescription = (description) => {
            let descriptionArray = {};
            if (typeof description === "string" && description.trim().length > 0) {
                let lines = description.trim().split("\n");
                lines.forEach(line => {
                    let parts = line.split(":", 2);
                    if (parts.length === 2) {
                        descriptionArray[$.trim(parts[0])] = $.trim(parts[1]);
                    }
                });
            }
            return descriptionArray;
        };
        // AJAX
        const sendBookingRequest = isDirectBooking => {
            let submitData = {};
            if (airline === "flyjinnah") {
                submitData = {
                    firstBundleId: firstBundleId ?? null,
                    secondBundleId: secondBundleId ?? null,
                    depSelectedFlight: depSelectedFlight ?? null,
                    rtnSelectedFlight: rtnSelectedFlight ?? null,
                    departureFlight: departureFlight ?? null,
                    returnFlight: returnFlightRaw ?? null,
                    isDirectBooking,
                    flightTotalFare,
                    segments,
                    paxCount,
                    airline,
                    _token: "{{ csrf_token() }}"
                }
            } else if (airline === "emirate") {
                submitData = {
                    firstBundleId: firstBundleId ? JSON.parse(decodeURIComponent(firstBundleId)) : null,
                    secondBundleId: secondBundleId ? JSON.parse(decodeURIComponent(secondBundleId)) :
                        null,
                    depOfferIds: offerIdsDep ? JSON.parse(decodeURIComponent(offerIdsDep)) : null,
                    rtnOfferIds: offerIdsRtn ? JSON.parse(decodeURIComponent(offerIdsRtn)) : null,
                    departureFlight: departureFlight ?? null,
                    returnFlight: returnFlight ?? null,
                    responseId,
                    airline,
                    paxCount,
                    _token: "{{ csrf_token() }}"
                }
            } else if (airline === "airblue") {
                // Parse bundle IDs
                const departureBundle = firstBundleId ? JSON.parse(decodeURIComponent(firstBundleId)) :
                    null;
                const returnBundle = secondBundleId ? JSON.parse(decodeURIComponent(secondBundleId)) : null;

                // Build flights array (same format as multiple-flights)
                const flightsData = [];

                if (departureFlight && departureBundle) {
                    // Deep clone the entire flight object and remove bundles property
                    const cleanDepartureFlight = JSON.parse(JSON.stringify(departureFlight));
                    if (cleanDepartureFlight.bundles) {
                        delete cleanDepartureFlight.bundles;
                    }

                    // Remove bundles and best_bundle from flightRaw if it exists
                    if (cleanDepartureFlight.flightRaw) {
                        if (cleanDepartureFlight.flightRaw.bundles) {
                            delete cleanDepartureFlight.flightRaw.bundles;
                        }
                        if (cleanDepartureFlight.flightRaw.best_bundle) {
                            delete cleanDepartureFlight.flightRaw.best_bundle;
                        }
                    }

                    flightsData.push({
                        departure: cleanDepartureFlight,
                        bundle: departureBundle
                    });
                }

                if (returnFlight && returnBundle) {
                    // Deep clone the entire flight object and remove bundles property
                    const cleanReturnFlight = JSON.parse(JSON.stringify(returnFlight));
                    if (cleanReturnFlight.bundles) {
                        delete cleanReturnFlight.bundles;
                    }

                    // Remove bundles and best_bundle from flightRaw if it exists
                    if (cleanReturnFlight.flightRaw) {
                        if (cleanReturnFlight.flightRaw.bundles) {
                            delete cleanReturnFlight.flightRaw.bundles;
                        }
                        if (cleanReturnFlight.flightRaw.best_bundle) {
                            delete cleanReturnFlight.flightRaw.best_bundle;
                        }
                    }

                    flightsData.push({
                        departure: cleanReturnFlight,
                        bundle: returnBundle
                    });
                }

                submitData = {
                    flights: flightsData,
                    airline: 'airblue',
                    paxCount: paxCount,
                    _token: "{{ csrf_token() }}"
                }
            } else if (airline === "pia") {
                submitData = {
                    outbound_bundle: firstBundleId,
                    inbound_bundle: secondBundleId || null,
                    offer_id: responseId,
                    total_price_pkr: selectedTotalPricePkr,
                    departureFlight: departureFlight ?? null,
                    returnFlight: returnFlight ?? null,
                    airline, paxCount, _token: "{{ csrf_token() }}"
                }
            }
            // console.log('ajax data =>', data);
            // return;
            $.ajax({
                type: "POST",
                url: "{{ route('booking_details') }}",
                data: submitData,
                beforeSend: () => _loader('show'),
                success: function(response) {
                    if (response.status === 'success' && response.redirect) {
                        localStorage.setItem('flights', window.location.search);
                        window.location.href = response.redirect;
                    } else {
                        _alert(response.message ?? 'Unknown error', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    _alert(xhr.responseJSON.message, 'error')
                    console.error('Error Details:', xhr.responseJSON.details);
                    console.error('Error:', error);
                },
                complete: () => _loader('hide')
            });
        };
        // PIA 
        // WORK HERE FOR PIA :)
        const renderPiaBundles = (el, isReturn) => {
            const $flightCard = $(el).closest(".flight-card");
            const $bundleSection = $flightCard.find(".bundle-section");
            const $bundleLoop = $flightCard.find(".bundle-loop");
            $bundleSection.slideToggle();

            // console.log('renderPiaBundles called', {isReturn, extras, bundles, departureFlight, returnFlightRaw});

            // Check if extras and combinations exist
            if (!extras || !extras.pia || !extras.pia.combinations) {
                setTimeout(() => {
                    $bundleLoop.html(
                        `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No PIA data available</div>`
                    );
                }, 400);
                return;
            }

            // Group bundles and find min price (Outbound) or exact price (Inbound)
            const combinations = extras.pia.combinations || [];
            
            let relevantCombos = [];
            const flightJourneyId = isReturn ? returnFlightRaw.flightRaw.journey_id : departureFlight.flightRaw.journey_id;
            
            // console.log('Flight Journey ID:', flightJourneyId, 'isReturn:', isReturn);
            
            if (!isReturn) {
                 // Outbound: Filter combinations that include this journey_id in their journeys object
                relevantCombos = combinations.filter(c => c.journeys && c.journeys.hasOwnProperty(flightJourneyId));
            } else {
                // Inbound: Filter by combinations that include the selected outbound bundle AND the current inbound journey
                relevantCombos = combinations.filter(c => 
                    c.journeys && 
                    c.journeys.hasOwnProperty(departureFlight.flightRaw.journey_id) &&
                    c.journeys[departureFlight.flightRaw.journey_id] === firstBundleId && // outbound bundle name must match
                    c.journeys.hasOwnProperty(flightJourneyId)
                );
            }
            
            // console.log('Relevant combos found:', relevantCombos.length, relevantCombos);
            
            if (relevantCombos.length === 0) {
                 setTimeout(() => {
                    $bundleLoop.html(
                        `<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">No valid bundles for this selection</div>`
                    );
                }, 400);
                return;
            }

            let uniqueBundles;
            
            if (!isReturn) {
                // OUTBOUND: Show ALL available bundles from the bundles collection
                // Not just the ones in combinations, but validate which are actually bookable
                uniqueBundles = Object.keys(bundles).map(bundleKey => {
                    const bundleDef = bundles[bundleKey];
                    const bundleName = bundleDef.name || bundleKey.toUpperCase();
                    
                    // Find the minimum price for this bundle with this journey
                    const combosForBundle = relevantCombos.filter(c => c.journeys[flightJourneyId] === bundleName);
                    
                    // console.log(`Bundle ${bundleName}: found ${combosForBundle.length} combos`);
                    if (combosForBundle.length > 0) {
                        // console.log(`  Prices: ${combosForBundle.map(c => c.total_price_pkr).join(', ')}`);
                    }
                    
                    if (combosForBundle.length === 0) {
                        // Bundle not available for this flight, but still show it
                        return {
                            name: bundleName,
                            minPrice: null,
                            combo: null,
                            available: false
                        };
                    }
                    
                    // Find minimum price using reduce for accuracy
                    const minCombo = combosForBundle.reduce((min, c) => 
                        parseFloat(c.total_price_pkr) < parseFloat(min.total_price_pkr) ? c : min
                    , combosForBundle[0]);
                    
                    // console.log(`  Min price selected: ${minCombo.total_price_pkr}`);
                    
                    return {
                        name: bundleName,
                        minPrice: parseFloat(minCombo.total_price_pkr),
                        combo: minCombo,
                        available: true
                    };
                });
            } else {
                // INBOUND: Only show bundles that are valid with the selected outbound bundle
                const grouped = {};
                relevantCombos.forEach(c => {
                     const bundleName = c.journeys[flightJourneyId]; // Get bundle name for this specific journey
                     if (!grouped[bundleName]) {
                         grouped[bundleName] = {
                             name: bundleName,
                             minPrice: parseFloat(c.total_price_pkr),
                             combo: c,
                             available: true
                         };
                     } else {
                         if (parseFloat(c.total_price_pkr) < grouped[bundleName].minPrice) {
                             grouped[bundleName].minPrice = parseFloat(c.total_price_pkr);
                             grouped[bundleName].combo = c;
                         }
                     }
                });
                uniqueBundles = Object.values(grouped);
            }
            
            // console.log('Unique bundles:', uniqueBundles);

             setTimeout(() => {
                const cardsHtml = uniqueBundles.map(item => {
                    
                    const bundleDef = bundles[item.name.toLowerCase()] || bundles[item.name] || {};
                    const displayName = bundleDef.display_name || item.name;
                    const benefits = bundleDef.included || [];
                    const baggageDesc = bundleDef.baggage_description || 'N/A';
                    const benefitsHtml = benefits.map(b => `<li>${b}</li>`).join('');

                    if (!item.available) {
                        // Bundle not available for this flight
                        return `
                            <div class="card h-100 shadow-sm mx-2" style="min-width: 250px; opacity: 0.5;">
                                <div class="card-header bg-secondary text-white fw-bold">
                                    ${displayName}
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled small">${benefitsHtml}</ul>
                                </div>
                                <div class="card-footer text-center bg-white">
                                    <button class="btn btn-secondary w-100 fw-bold" disabled>
                                        Not Available
                                    </button>
                                </div>
                            </div>
                        `;
                    }

                    // Calculate price display based on if it's a return flight
                    let priceDisplay;
                    if (isReturn && selectedDepartureBundlePrice > 0) {
                        // For return flights, show the difference from selected departure bundle
                        const priceDiff = Math.round(item.minPrice - selectedDepartureBundlePrice);
                        priceDisplay = priceDiff >= 0 ? `+${formatCurrency(priceDiff)}` : `-${formatCurrency(Math.abs(priceDiff))}`;
                    } else {
                        // For departure flights, show the full price
                        priceDisplay = formatCurrency(Math.round(item.minPrice));
                    }
                    const price = formatCurrency(Math.round(item.minPrice));
                    
                    return `
                        <div class="card h-100 shadow-sm mx-2" style="min-width: 250px;">
                            <div class="card-header bg-light fw-bold">
                                ${displayName}
                            </div>
                            <div class="card-body small">
                                <div class="baggage-summary mb-2">
                                    <strong>Baggage:</strong> ${baggageDesc}
                                </div>
                                <ul class="list-unstyled">${benefitsHtml}</ul>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <button
                                    class="btn btn-primary w-100 fw-bold bookBtn"
                                    data-airline="pia"
                                    data-is-return="${isReturn}"
                                    data-bundle-name="${item.name}" 
                                    data-combo-key="${item.combo?.key || ''}"
                                    data-recommended-id="${item.combo?.recommended_offer_id || ''}"
                                    data-bundle-price="${item.minPrice}"
                                    data-total-price="${item.combo?.total_price_pkr || item.minPrice}"
                                >
                                    PKR ${priceDisplay}
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                $bundleLoop.html(`
                    <div class="fare-scroll d-flex overflow-auto pb-3">${cardsHtml}</div>
                `);
            }, 500);
        }






        // Airblue
        const renderAirblueBundles = (data, el, isReturn) => {
            const $flightCard = $(el).closest(".flight-card");
            const $bundleSection = $flightCard.find(".bundle-section");
            const $bundleLoop = $flightCard.find(".bundle-loop");
            $bundleSection.slideToggle();

            if (!data || data.length === 0) {
                setTimeout(() => {
                    $bundleLoop.html(`<div class="w-100 bg-body-secondary text-dark-emphasis rounded-2 text-center py-2">
                        No bundles available for Airblue
                    </div>`);
                }, 400);
                return;
            }

            const normalizedData = Array.isArray(data) ? data : [data];

            setTimeout(() => {
                const cardsHtml = normalizedData.map(row => {
                    // console.log(row)
                    const bulletPoints = `
                        <li>Baggage: ${row.baggage}</li>
                        <li>Meals: Included</li>
                        <li>Seat Selection: Mandatory with standard charges</li>
                        <li>Refunds & Exchanges: Allowed with Higher Fee</li>
                    `;

                    return `
                        <div class="card h-100 shadow-sm mx-2">
                            <div class="card-header bg-light fw-bold">
                                ${row.bundle_name}
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">${bulletPoints}</ul>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <button
                                    class="btn btn-primary w-100 fw-bold bookBtn"
                                    data-airline="airblue"
                                    data-is-return="${isReturn}"
                                    data-bundle-id="${encodeURIComponent(JSON.stringify(row))}"
                                >
                                    PKR ${formatCurrency(row.total_price)}
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                $bundleLoop.html(`
                    <div class="fare-scroll d-flex overflow-auto pb-3">${cardsHtml}</div>
                `);
            }, 500);
        };

    });
</script>
