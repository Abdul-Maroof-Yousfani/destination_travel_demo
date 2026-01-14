{{-- @dd($flightData, $totalFare, $tax) --}}
@if (!empty($flightData) && isset($flightData) && isset($flightData['departure']))
    @php
        $logo = $flightData['airline'] ?? 'default';

        $departure = $flightData['departure'] ?? null;
        $originCode = $departure['departure']['code'] ?? '';
        $destinationCode = $departure['arrival']['code'] ?? '';
        $depTime = $departure['departure']['time'] ?? '';
        $arrTime = $departure['arrival']['time'] ?? '';
        $depTimeDiff = $departure['duration'] ?? '';
        $isConnected = $departure['isConnected'] === 'true';
        $stopCount = count($departure['segments']) - 1;

        $hasReturn = isset($flightData['return']);
        if ($hasReturn) {
            $return = $flightData['return'];
            $returnOriginCode = $return['departure']['code'] ?? '';
            $returnDestinationCode = $return['arrival']['code'] ?? '';
            $returnDepTime = $return['departure']['time'] ?? '';
            $returnArrTime = $return['arrival']['time'] ?? '';
            $returnTimeDiff = $return['duration'] ?? '';
            $returnIsConnected = $return['isConnected'] === 'true';
            $returnStopCount = count($return['segments']) - 1;
        }

        $depDate = $departure['departure']['date'] ?? '';
    @endphp
    <div class="steps">
        <h4>Your Booking</h4>
        <div class="sugge-tab sugge-tab-tickes">
            <div class="flex1">
            <div class="emri w-25">
                <img src="{{ asset('assets/images/logos/modal/' . $logo . '.png') }}" alt="{{ strtoupper($logo) }}">
            </div>   
            <div class="der-time der-time-setps">
                <ul>
                    <li><h2>{{ $depTime }}</h2></li>
                    <li><div class="stays"><p>{{ $depTimeDiff }}</p></div></li>
                    <li><div class="tims"><h2>{{ $arrTime }}</h2></div></li>
                </ul>
                <div class="citys">
                    <div class="cit">
                        <ul>
                            <li><p>{{ $originCode }}</p></li>
                            <li><p>-</p></li>
                            @if ($isConnected)
                                <li><p>{{ $stopCount }} {{ $stopCount > 1 ? 'Stops' : 'Stop' }}</p></li>
                            @else
                                <li><p>Nonstop</p></li>
                            @endif
                            <li><p>-</p></li>
                            <li><p>{{ $destinationCode }}</p></li>
                        </ul>
                    </div>
                </div>
            </div>
            </div>
        </div>
        @if ($hasReturn)
            <div class="sugge-tab sugge-tab-tickes mt-2">
                <div class="flex1">
                    <div class="emri w-25">
                    <img src="{{ asset('assets/images/logos/modal/' . $logo . '.png') }}" alt="{{ strtoupper($logo) }}">
                    </div>   
                    <div class="der-time der-time-setps">
                    <ul>
                        <li><h2>{{ $returnDepTime }}</h2></li>
                        <li><div class="stays"><p>{{ $returnTimeDiff }}</p></div></li>
                        <li><div class="tims"><h2>{{ $returnArrTime }}</h2></div></li>
                    </ul>
                    <div class="citys">
                        <div class="cit">
                            <ul>
                                <li><p>{{ $returnOriginCode }}</p></li>
                                <li><p>-</p></li>
                                @if ($returnIsConnected)
                                    <li><p>{{ $returnStopCount }} {{ $returnStopCount > 1 ? 'Stops' : 'Stop' }}</p></li>
                                @else
                                    <li><p>Nonstop</p></li>
                                @endif
                                <li><p>-</p></li>
                                <li><p>{{ $returnDestinationCode }}</p></li>
                            </ul>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
