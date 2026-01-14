<!-- Multiple Flights (Multi-City) -->
@php
    use Carbon\Carbon;
    use App\Helpers\HelperFunctions;
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

    .price-btn {
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

    .price-btn {
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

    .leg-section {
        margin-bottom: 40px;
    }

    .leg-header {
        background: #f8f9fa;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #004080;
    }

    .leg-header h4 {
        margin: 0;
        color: #004080;
    }
</style>

@if (!empty($flightData) && isset($flightData['legs']))
    @php
        $legs = $flightData['legs'];
        $paxCount = $paxCount ?? 1;
        $hasFlights = false;
    @endphp

    @foreach ($legs as $legIndex => $flights)
        @php
            $firstFlight = $flights->first();
            if (!$firstFlight) {
                continue;
            }

            $from = $firstFlight['departure']['code'] ?? '';
            $to = $firstFlight['arrival']['code'] ?? '';
            $date = Carbon::parse($firstFlight['departure']['datetime'])->format('D, d M Y');
            $legHasAirblue = false;
        @endphp

        <div class="leg-section" id="leg-section-{{ $legIndex }}" data-leg="{{ $legIndex }}"
            style="{{ $legIndex > 1 ? 'display:none;' : '' }}">
            {{-- <div class="leg-header">
                <h4>
                    <strong>Leg {{ $legIndex }}:</strong> {{ $from }} → {{ $to }}
                    <span class="text-muted ms-2">{{ $date }}</span>
                </h4>
            </div> --}}

            @forelse ($flights as $flight)
                @php
                    // Only show airblue flights
                    if (strtolower($flight['carrier'] ?? '') !== 'airblue') {
                        continue;
                    }

                    $hasFlights = true;
                    $legHasAirblue = true;
                    $logo = strtolower($flight['carrier']);
                    $flightDep = $flight['departure'];
                    $flightArr = $flight['arrival'];
                    $firstSegment = $flight['segments'][0] ?? $flight['segments'];
                    $stopCount = count($flight['segments']) - 1;
                @endphp

                <div class="flight-card" data-leg="{{ $legIndex }}">
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
                        </div>

                        <!-- Price Info -->
                        <div class="col-12 col-md-2 text-md-end">
                            <button class="price-btn mb-2 multi-flight-btn" data-leg="{{ $legIndex }}"
                                data-flight="{{ json_encode($flight) }}">
                                {{ $flight['code'] ?? 'PKR' }} {{ number_format($flight['price'] ?? 0) }}
                            </button>
                            <div class="text-muted small roundtrip">Multi-City</div>
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
                @if (!$legHasAirblue)
                    <p class="text-center text-muted">No airblue flights available for this leg.</p>
                @endif
            @endforelse
        </div>
    @endforeach

    @if ($hasFlights ?? false)
        <!-- Proceed Button -->
        <div class="text-center mt-5" id="proceed-button-container" style="display:none;">
            <button id="proceed-multi-booking" class="btn btn-secondary btn-lg px-5" disabled>
                Proceed to Passenger Details
            </button>
        </div>
    @endif
@endif

<script>
    $(document).ready(function() {
        let paxCount = @json($paxCount ?? 1);
        let selectedFlights = {}; // { legIndex: { flight: {}, bundle: {} } }
        let totalLegs = {{ count($flightData['legs'] ?? []) }};

        // Toggle connected flight details
        $('.connected').off('click').on('click', function() {
            $(this).closest(".flight-card").find(".details-section").slideToggle();
        });

        // Handle price button click for multi-flight
        $(".multi-flight-btn").click(function() {
            const legIndex = $(this).data('leg');
            const flight = $(this).data('flight');
            const selectedCarrier = flight.carrier;

            // Only handle airblue
            if (selectedCarrier !== 'airblue') {
                _alert('Only airblue flights are supported for multi-city bookings', 'warning');
                return;
            }

            // Store selected flight for this leg
            if (!selectedFlights[legIndex]) {
                selectedFlights[legIndex] = {};
            }
            selectedFlights[legIndex].flight = flight;

            // Render bundles
            renderAirblueBundles(flight.bundles || [], this, legIndex);
        });

        // Render Airblue Bundles
        const renderAirblueBundles = (data, el, legIndex) => {
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
                                    class="btn btn-primary w-100 fw-bold multi-bookBtn"
                                    data-airline="airblue"
                                    data-leg="${legIndex}"
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

        // Handle bundle selection
        $(document).on('click', '.multi-bookBtn', function() {
            const legIndex = parseInt($(this).data('leg'));
            const bundle = JSON.parse(decodeURIComponent($(this).data('bundle-id')));
            const airline = $(this).data('airline');

            if (airline !== 'airblue') {
                _alert('Only airblue flights are supported', 'warning');
                return;
            }

            // Store selected bundle for this leg
            if (!selectedFlights[legIndex]) {
                selectedFlights[legIndex] = {};
            }
            selectedFlights[legIndex].bundle = bundle;

            // Highlight selected bundle
            $(this).closest('.flight-card').find('.multi-bookBtn').removeClass('btn-success').addClass(
                'btn-primary');
            $(this).removeClass('btn-primary').addClass('btn-success').text('Selected ✓');

            // Update step indicators (similar to return flights flow)
            $(`#leg-text-${legIndex}`).removeClass('active');
            const nextLegIndex = legIndex + 1;
            const nextLegText = $(`#leg-text-${nextLegIndex}`);

            // Hide current leg and show next leg (similar to return flights flow)
            const currentLegSection = $(`#leg-section-${legIndex}`);
            const nextLegSection = $(`#leg-section-${nextLegIndex}`);

            if (nextLegSection.length) {
                // Update step indicator for next leg
                nextLegText.addClass('active');

                // Slide up current leg and fade in next leg
                currentLegSection.slideUp(500, function() {
                    nextLegSection.fadeIn(500);
                    $('html, body').animate({
                        scrollTop: nextLegSection.offset().top - 100
                    }, 600);
                });
            } else {
                // This is the last leg, show proceed button
                currentLegSection.slideUp(500, function() {
                    $('html, body').animate({
                        scrollTop: $('#proceed-multi-booking').offset().top - 100
                    }, 600);
                });
            }

            // Check if all legs have flights and bundles selected
            checkAllSelected();
        });

        function checkAllSelected() {
            const allSelected = Object.keys(selectedFlights).length === totalLegs &&
                Object.values(selectedFlights).every(item => item.flight && item.bundle);

            if (allSelected) {
                $('#proceed-button-container').fadeIn();
                $('#proceed-multi-booking').prop('disabled', false).removeClass('btn-secondary').addClass(
                    'btn-success');
            } else {
                $('#proceed-multi-booking').prop('disabled', true).removeClass('btn-success').addClass(
                    'btn-secondary');
            }
        }

        // Final submit for multi-flight booking
        $(document).on('click', '#proceed-multi-booking', function() {
            if ($(this).prop('disabled')) return;

            // Prepare data similar to single flight booking
            const flightsData = [];

            // Sort by leg index
            const sortedLegs = Object.keys(selectedFlights).sort((a, b) => parseInt(a) - parseInt(b));

            sortedLegs.forEach(legIndex => {
                const legData = selectedFlights[legIndex];
                if (legData.flight && legData.bundle) {
                    // Deep clone the entire flight object and remove bundles property
                    const cleanFlight = JSON.parse(JSON.stringify(legData.flight));
                    if (cleanFlight.bundles) {
                        delete cleanFlight.bundles;
                    }

                    // Remove bundles and best_bundle from flightRaw if it exists
                    if (cleanFlight.flightRaw) {
                        if (cleanFlight.flightRaw.bundles) {
                            delete cleanFlight.flightRaw.bundles;
                        }
                        if (cleanFlight.flightRaw.best_bundle) {
                            delete cleanFlight.flightRaw.best_bundle;
                        }
                    }

                    flightsData.push({
                        departure: cleanFlight,
                        bundle: legData.bundle
                    });
                }
            });

            const data = {
                flights: flightsData,
                airline: 'airblue',
                paxCount: paxCount,
                _token: "{{ csrf_token() }}"
            };
            // console.log(data)
            // return;

            $.ajax({
                type: "POST",
                url: "{{ route('booking_details') }}",
                data,
                beforeSend: () => {
                    _loader('show');
                    $(this).prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm"></span> Processing...'
                    );
                },
                success: function(response) {
                    if (response.redirect) {
                        localStorage.setItem('flights', window.location.search);
                        window.location.href = '/flights/booking';
                    } else if (response.error) {
                        _alert(response.error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    _alert(xhr.responseJSON?.message || 'Booking failed', 'error');
                    console.error('Error Details:', xhr.responseJSON?.details);
                    console.error('Error:', error);
                },
                complete: () => {
                    _loader('hide');
                    $('#proceed-multi-booking').prop('disabled', false).html(
                        'Proceed to Passenger Details');
                }
            });
        });
    });
</script>
