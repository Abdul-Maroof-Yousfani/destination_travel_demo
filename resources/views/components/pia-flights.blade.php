@php
    $labels = ['Departure', 'Return', 'Segment 3', 'Segment 4']; // Extendable for more segments
@endphp

@if (!empty($flights) && !isset($flights['error']))
    <div class="container mx-auto px-4">
        <div class="row mt-5 flex flex-wrap">
            @foreach ($flights[0]['journeys'] as $index => $journey)
                <div class="col-sm-{{ count($flights[0]['journeys']) > 1 ? '6' : '12' }} col-12 px-4">
                    <div class="flex mb-3">
                        <p class="font-bold mr-2">{{ $labels[$index] ?? 'Flight Segment' }}</p>
                        <i><p>{{ $journey['segments'][0]['origin'] }} → {{ $journey['segments'][last(array_keys($journey['segments']))]['destination'] }}</p></i>
                    </div>
                    @foreach ($flights as $flightIndex => $flight)
                        <div class="flight-item {{ $flightIndex > 0 ? 'hidden extra-flight-pia' : '' }}">
                            <div class="prices2 flex items-center mb-2">
                                <input 
                                    type="radio"
                                    id="singleFlightPia{{ $index }}_{{ $flightIndex }}"
                                    value="{{ $flight['total_price']['total_amount'] ?? 0 }}"
                                    name="piaFlight{{ $index }}"
                                    {{ $flightIndex == 0 ? 'checked' : '' }}
                                    onchange="updateTotalPrice()"
                                    data-segment="{{ json_encode($flight['journeys'][$index]['segments']) }}"
                                    data-response-id="{{ json_encode($flight['offer_id']) }}"
                                >
                                <label class="flex-1" for="singleFlightPia{{ $index }}_{{ $flightIndex }}">
                                    <div class="emri text-center">
                                        <img class="w-20 p-2 mx-auto" src="assets/images/pia-logo.png" alt="Pakistan Airlines">
                                    </div>
                                    <div class="der-time">
                                        <ul class="flex justify-between">
                                            <li>
                                                <h2 class="text-lg font-bold">{{ date('H:i', strtotime($journey['segments'][0]['departure_time'])) }}</h2>
                                            </li>
                                            <li>
                                                <div class="stays">
                                                    <p>
                                                        @php
                                                            $totalMinutes = array_reduce($journey['segments'], function($sum, $seg) {
                                                                list($hours, $minutes) = sscanf($seg['duration'], '%dh %dm');
                                                                return $sum + $hours * 60 + $minutes;
                                                            }, 0);
                                                            echo sprintf('%dh %dm', floor($totalMinutes / 60), $totalMinutes % 60);
                                                        @endphp
                                                    </p>
                                                </div>
                                            </li>
                                            <li>
                                                <h2 class="text-lg font-bold">{{ date('H:i', strtotime($journey['segments'][last(array_keys($journey['segments']))]['arrival_time'])) }}</h2>
                                            </li>
                                        </ul>
                                        <div class="citys">
                                            <div class="cit">
                                                <ul class="flex justify-between">
                                                    <li><p>{{ $journey['segments'][0]['origin'] }}</p></li>
                                                    <li><p>-</p></li>
                                                    <li><p>{{ count($journey['segments']) > 1 ? (count($journey['segments']) - 1) . ' Stop' : 'Nonstop' }}</p></li>
                                                    <li><p>-</p></li>
                                                    <li><p>{{ $journey['segments'][last(array_keys($journey['segments']))]['destination'] }}</p></li>
                                                </ul>
                                                <div class="weig weig2">
                                                    <ul>
                                                        <li>
                                                            <p>
                                                                <i class="fa-solid fa-money-bill-wave"></i> 
                                                                {{ $flight['total_price']['currency'] }} {{ $flight['total_price']['total_amount'] }}
                                                            </p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <hr>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="text-center mb-3 col-12">
                <span class="text-info font-bold cursor-pointer toggle-flights-btn" data-target=".extra-flight-pia">Show more flights</span>
            </div>
        </div>
        <div class="prices2 mt-3">
            <div class="select-flight">
                <button class="btn bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 bundleModalBtnPia">
                    PKR <span id="totalPiaPrice">0</span> - Select flight
                </button>
            </div>
        </div>
    </div>

    <!-- Modal for flight details -->
    <div class="modal fade" id="bundleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flight Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modalFlights">
                    <!-- Flight details will be injected here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let updateTimeoutPia;
            let selectedSegments = {};
            let responseId;

            function updateTotalPrice() {
                clearTimeout(updateTimeoutPia);
                updateTimeoutPia = setTimeout(() => {
                    let totalPrice = 0;
                    responseId = null;

                    @foreach ($flights[0]['journeys'] as $index => $journey)
                        let selectedFlight{{ $index }} = $('input[name="piaFlight{{ $index }}"]:checked');
                        totalPrice += parseFloat(selectedFlight{{ $index }}.val()) || 0;
                        if (selectedFlight{{ $index }}.data('response-id')) {
                            responseId = selectedFlight{{ $index }}.data('response-id').replace(/^"|"$/g, '');
                        }
                        selectedSegments[{{ $index }}] = selectedFlight{{ $index }}.data('segment');
                    @endforeach

                    $('#totalPiaPrice').text(formatCurrency(totalPrice));
                }, 100);
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(amount);
            }

            function flightHtml(segments, airline) {
                let html = '';
                Object.keys(segments).forEach((index, i) => {
                    html += `<h3 class="font-bold">${['Departure', 'Return', 'Segment 3', 'Segment 4'][i] || 'Flight Segment'}</h3>`;
                    segments[index].forEach(segment => {
                        html += `
                            <div class="mb-4">
                                <p>Flight ${segment.flight_number}: ${segment.origin} → ${segment.destination}</p>
                                <p>Departure: ${segment.departure_time}</p>
                                <p>Arrival: ${segment.arrival_time}</p>
                                <p>Duration: ${segment.duration}</p>
                                <p>Baggage: ${segment.baggage_allowance.weight} ${segment.baggage_allowance.unit}</p>
                            </div>`;
                    });
                });
                return html;
            }

            $(document).on('change', 'input[name^="piaFlight"]', updateTotalPrice);

            $('.toggle-flights-btn').click(function() {
                $($(this).data('target')).toggleClass('hidden');
                $(this).text($(this).text() === 'Show more flights' ? 'Show fewer flights' : 'Show more flights');
            });

            $('.bundleModalBtnPia').click(function() {
                $('.modalFlights').html(flightHtml(selectedSegments, 'pia'));
                $('#bundleModal').modal('show');
            });

            updateTotalPrice();
        });
    </script>
@else
    <p class="text-center text-red-500">Pakistan International Airlines flights not available</p>
@endif