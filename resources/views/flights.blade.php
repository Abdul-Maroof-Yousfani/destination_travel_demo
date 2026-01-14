<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title>Flights</title>
    <style>
        .pointer {
            cursor: pointer;
        }
    </style>
</head>
<body>
    {{-- @dd($data) --}}
    <h1>Search Flight</h1>
    <x-searchflightold/>
    <div class="m-3 p-3">
        @if ($isRoundTrip)
            <h1 class="mb-3">Round Trip Flight</h1>
        @else
            <h1 class="mb-3">Direct Flight</h1>
        @endif
        @if ((count($data) > 0) && $data[0])
            <div class="d-flex justify-content-between">
                <h3>{{ $data[0]['route'] }}</h3>
                <h3>{{ $data[0]['date'] }}</h3>
            </div>
            @foreach ($data[0]['flights'] as $key => $flight1)
            {{-- @dd($flight1) --}}
                <div class="accordion shadow-lg m-3" id="flightAccordian{{$key}}">
                    <div class="accordion-item">
                        <h2 class="accordion-header fs-6">
                            <div class="row">
                                <div class="col-8 p-4">
                                    @php
                                        $item = $flight1['flightSegments'];
                                        $segment1 = $item[0]['origin']['airportCode'] ?? null;
                                        $segment2 = $item[1]['destination']['airportCode'] ?? ($item[0]['destination']['airportCode'] ?? null);

                                        $arrivalDateTime = \Carbon\Carbon::parse($item[0]['arrivalDateTimeLocal']) ?? null;
                                        $departureDateTime = \Carbon\Carbon::parse($item[1]['departureDateTimeLocal'] ?? $item[0]['departureDateTimeLocal']) ?? null;

                                        $departureDayIncrease = $arrivalDateTime->toDateString() !== $departureDateTime->toDateString();

                                        $arrivalTime = $arrivalDateTime->format('h:i A');
                                        $departureTime = $departureDateTime->format('h:i A');

                                        $price = $flight1['cabinPrices'][0]['price'] ?? null;
                                        // dd($flight1);
                                    @endphp
                                    <div class="p-2 border border-4 m-2 d-flex justify-content-between">
                                        <div>
                                            <p class="m-0">From</p>
                                            <h3>({{$segment1}})</h3>
                                            <h4>{{ $arrivalTime }}</h4>
                                        </div>
                                        <div>
                                            <img src="{{ url('assets/images/right_arrow.png') }}" alt="icon">
                                            <br>
                                            @if (count($item) > 1)
                                                <a href="#">1 Stop</a>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="m-0">To</p>
                                            <h3>({{$segment2}})</h3>
                                            @if ($departureDayIncrease != 0)
                                                <sup class="float-end text-primary">+ {{$departureDayIncrease}}</sup>
                                            @endif
                                            <h4>{{ $departureTime }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 p-4">
                                    <div>
                                        <h5>Total Price</h5>
                                        <h2 class="text-primary-emphasis text-uppercase">{{round($price ?? 0)}}</h2>
                                        {{-- <h6 class="pointer mt-3 btn btn-dark" data-bs-toggle="collapse" data-bs-target="#collapseFlight{{$key}}" aria-expanded="false">
                                            Show details
                                        </h6> --}}
                                        <div class="w-25">
                                            <button type="button" class="mt-3 mb-2 btn btn-primary w-100" onclick="bookFirstBundle({{ json_encode($flight1['flightSegments'])}})">Select This</button>
                                        </div>
                                        <h6 class="badge text-bg-primary"> {{$flight1['availabilityStatus']}}</h6>
                                        <h6 class="badge text-bg-primary"> {{$flight1['flightSegments'][0]['flightNumber'] ?? null}} {{ isset($flight1['flightSegments'][1]) ? '/' . $flight1['flightSegments'][1]['flightNumber'] : '' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </h2>
                        {{-- <div id="collapseFlight{{$key}}" class="border border-top accordion-collapse collapse" data-bs-parent="#flightAccordian{{$key}}">
                            <div class="accordion-body">
                                <div class="row p-4">
                                    @if ($flight1['bundles']['error'])
                                        <div class="alert alert-danger" role="alert">
                                            {{ $flight1['bundles']['error'] }}
                                        </div>
                                    @elseif (isset($flight1['bundles']['bundles']['bundledService']))
                                        @foreach ($flight1['bundles']['bundles']['bundledService'] as $key => $option)
                                        @php
                                            $descriptionArray = [];
                                            if (isset($option['description']) && is_string($option['description'])) {
                                                $lines = explode("\n", trim($option['description']));

                                                foreach ($lines as $line) {
                                                    $parts = explode(':', $line, 2);
                                                    if (count($parts) == 2) {
                                                        $descriptionArray[trim($parts[0])] = trim($parts[1]);
                                                    }
                                                }
                                            }
                                            $jsessionId = $flight1['bundles']['JSESSIONID'];
                                            $transactionId = $flight1['bundles']['TransactionIdentifier'];
                                            $flightSegment = $flight1['bundles']['originDestinationOptions'];
                                        @endphp
                                            <div class="col-auto my-4">
                                                <div class="card" style="width: 18rem;">
                                                    <div class="card-body">
                                                        <table class="table">
                                                            <thead class="thead-dark">
                                                                <tr>
                                                                    <th class="badge text-bg-primary">{{$option['bundledServiceName']}}</th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="badge text-bg-primary">{{$option['bunldedServiceId']}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td><strong>Booking Classes</strong></td>
                                                                    <td>{{$option['bookingClasses']}}</td>
                                                                </tr>
                                                                <tr colspan='2'>
                                                                    <td>
                                                                        <strong class="d-block">Description</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Baggage: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{$descriptionArray['Baggage'] ?? 'Not Included'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Meal: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{$descriptionArray['Meal'] ?? 'Not Included'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Modification: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{isset($descriptionArray['Modification']) ? 'Available' : 'Not Available'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <sup>{{$price['CurrencyCode'] ?? ''}} {{round(($price['Amount'] + $option['perPaxBundledFee']) ?? 0)}}</sup>
                                                        <button type="button" class="mt-3 mb-2 btn btn-primary w-100" data-transaction-id="{{$transactionId}}" data-jsession-id="{{$jsessionId}}" data-bundle-id="{{ $option['bunldedServiceId'] }}" onclick="firstFlight(this, {{ json_encode($flightSegment)}} )">Book Now</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @else
                                        <div class="alert alert-danger" role="alert">
                                            No flights available
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            @endforeach
        @endif
        @if (isset($data[1]) && $data[1])
            <div class="d-flex justify-content-between">
                <h3>{{ $data[1]['route'] }}</h3>
                <h3>{{ $data[1]['date'] }}</h3>
            </div>
            @foreach ($data[1]['flights'] as $key => $flight2)
                <div class="accordion shadow-lg m-3" id="flightAccordian{{$key}}">
                    <div class="accordion-item">
                        <h2 class="accordion-header fs-6">
                            <div class="row">
                                <div class="col-8 p-4">
                                    @php
                                        $item = $flight2['flightSegments'];
                                        $segment1 = $item[0]['origin']['airportCode'] ?? null;
                                        $segment2 = $item[1]['destination']['airportCode'] ?? ($item[0]['destination']['airportCode'] ?? null);

                                        $arrivalDateTime = \Carbon\Carbon::parse($item[0]['arrivalDateTimeLocal']) ?? null;
                                        $departureDateTime = \Carbon\Carbon::parse($item[1]['departureDateTimeLocal'] ?? $item[0]['departureDateTimeLocal']) ?? null;

                                        $departureDayIncrease = $arrivalDateTime->toDateString() !== $departureDateTime->toDateString();

                                        $arrivalTime = $arrivalDateTime->format('h:i A');
                                        $departureTime = $departureDateTime->format('h:i A');

                                        $price = $flight2['cabinPrices'][0]['price'] ?? null;
                                    @endphp
                                    <div class="p-2 border border-4 m-2 d-flex justify-content-between">
                                        <div>
                                            <p class="m-0">From</p>
                                            <h3>({{$segment1}})</h3>
                                            <h4>{{ $arrivalTime }}</h4>
                                        </div>
                                        <div>
                                            <img src="{{ url('assets/images/right_arrow.png') }}" alt="icon">
                                            <br>
                                            @if (count($item) > 1)
                                                <a href="#">1 Stop</a>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="m-0">To</p>
                                            <h3>({{$segment2}})</h3>
                                            @if ($departureDayIncrease != 0)
                                                <sup class="float-end text-primary">+ {{$departureDayIncrease}}</sup>
                                            @endif
                                            <h4>{{ $departureTime }}</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 p-4">
                                    <div>
                                        <h5>Total Price</h5>
                                        <h2 class="text-primary-emphasis text-uppercase">{{round($price ?? 0)}}</h2>
                                        {{-- <h6 class="pointer mt-3 btn btn-dark" data-bs-toggle="collapse" data-bs-target="#collapseReturnFlight{{$key}}" aria-expanded="false">
                                            Show details
                                        </h6> --}}
                                        <div class="w-25">
                                            <button type="button" class="mt-3 mb-2 btn btn-primary w-100" onclick="bookSecondBundle({{ json_encode($flight2['flightSegments'])}})">Select This</button>
                                        </div>
                                        <h6 class="badge text-bg-primary"> {{$flight2['availabilityStatus']}}</h6>
                                        <h6 class="badge text-bg-primary"> {{$flight2['flightSegments'][0]['flightNumber']}} / {{ isset($flight2['flightSegments'][1]) ? '/' . $flight2['flightSegments'][1]['flightNumber'] : '' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </h2>
                        {{-- <div id="collapseReturnFlight{{$key}}" class="border border-top accordion-collapse collapse" data-bs-parent="#flightAccordian{{$key}}">
                            <div class="accordion-body">
                                <div class="row p-4">
                                    @if ($flight2['bundles']['error'])
                                        <div class="alert alert-danger" role="alert">
                                            {{ $flight2['bundles']['error'] }}
                                        </div>
                                    @elseif (isset($flight2['bundles']['bundles']['bundledService']))
                                        @foreach ($flight2['bundles']['bundles']['bundledService'] as $key => $option)
                                            @php
                                                $descriptionArray = [];
                                                if (isset($option['description']) && is_string($option['description'])) {
                                                    $lines = explode("\n", trim($option['description']));

                                                    foreach ($lines as $line) {
                                                        $parts = explode(':', $line, 2);
                                                        if (count($parts) == 2) {
                                                            $descriptionArray[trim($parts[0])] = trim($parts[1]);
                                                        }
                                                    }
                                                }
                                                $jsessionId = $flight2['bundles']['JSESSIONID'];
                                                $transactionId = $flight2['bundles']['TransactionIdentifier'];
                                                $flightSegment = $flight2['bundles']['originDestinationOptions'];
                                            @endphp
                                            <div class="col-auto my-4">
                                                <div class="card" style="width: 18rem;">
                                                    <div class="card-body">
                                                        <table class="table">
                                                            <thead class="thead-dark">
                                                                <tr>
                                                                    <th class="badge text-bg-primary">{{$option['bundledServiceName']}}</th>
                                                                </tr>
                                                                <tr>
                                                                    <th class="badge text-bg-primary">{{$option['bunldedServiceId']}}</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td><strong>Booking Classes</strong></td>
                                                                    <td>{{$option['bookingClasses']}}</td>
                                                                </tr>
                                                                <tr colspan='2'>
                                                                    <td>
                                                                        <strong class="d-block">Description</strong>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Baggage: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{$descriptionArray['Baggage'] ?? 'Not Included'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Meal: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{$descriptionArray['Meal'] ?? 'Not Included'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Modification: </td>
                                                                    <td>
                                                                        <small>
                                                                            {{isset($descriptionArray['Modification']) ? 'Available' : 'Not Available'}}
                                                                        </small>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <sup>{{$price['CurrencyCode'] ?? ''}} {{round(($price['Amount'] + $option['perPaxBundledFee']) ?? 0)}}</sup>
                                                        <button type="button" class="mt-3 mb-2 btn btn-primary w-100" data-transaction-id="{{$transactionId}}" data-jsession-id="{{$jsessionId}}" data-bundle-id="{{ $option['bunldedServiceId'] }}" onclick="returnFlight(this, {{ json_encode($flightSegment) }})">Book Now</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        @else
                                        <div class="alert alert-danger" role="alert">
                                            No flights available
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
            @endforeach
        @endif
        <div class="directBundles row"></div>
        @if ($isRoundTrip)
            <h3>Return Flight Bundles</h3>
            <div class="returnBundles row"></div>
        @endif
    </div>
    <script>
        let isReturn = @json($isRoundTrip) ? true : false;
        let paxCount = @json($paxCount);
        
        let firstFlight, firstConnectedFlight, returnFlight, returnConnectedFlight;
        let segments, segmentsData;
        let firstBundleId, secondBundleId;
    
        /** 
         * Function to book the first bundle 
         */
        const bookFirstBundle = (data) => {
            firstFlight = getFlightData(data[0]);
            firstConnectedFlight = getFlightData(data[1] || null);
            if (isReturn) {
                alert('Now select return flight also');
            } else {
                getFlightBundle();
            }
        };
    
        /** 
         * Function to book the return flight 
         */
        const bookSecondBundle = (data) => {
            returnFlight = getFlightData(data[0]);
            returnConnectedFlight = getFlightData(data[1] || null);
            if (!firstFlight) {
                alert('Please select the first flight');
                return;
            }
            getFlightBundle();
        };
    
        /**
         * Function to fetch flight bundles from the server
         */
        const getFlightBundle = () => {
            _loader('show');
    
            $.post({
                url: "{{route('demo_get_bundles')}}",
                data: {
                    firstFlight,
                    firstConnectedFlight,
                    returnFlight,
                    returnConnectedFlight,
                    paxCount,
                    _token: "{{ csrf_token() }}"
                },
                success: function (res) {
                    if (res.error) {
                        alert(res.details?.ShortText || res.error);
                        return;
                    }
    
                    if (!res.bundles || res.bundles.length === 0 || (!res.bundles.bundledService && !res.bundles[0]?.bundledService)) {
                        $(".directBundles").html(`<div class="alert alert-danger" role="alert">No bundles available</div>`);
                        return;
                    }

                    segments = getSegment(res.originDestinationOptions.FlightSegment) || res.originDestinationOptions.map(item => getSegment(item.FlightSegment));

                    let bundledService = res.bundles[0]?.bundledService || res.bundles.bundledService;
                    $(".directBundles").html(renderBundles(bundledService, false));
    
                    if (res.bundles.length > 1) {
                        $(".returnBundles").html(renderBundles(res.bundles[1].bundledService, true));
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                },
                complete: function () {
                    _loader('hide');
                }
            });
        };
    
        /** 
         * Function to show/hide loader 
         */
        const _loader = (action) => {
            if (action === 'show') {
                $("body").append(`
                    <div id="loader" class="w-100 bg-dark vh-100 bg-opacity-25 position-fixed top-0 z_inf">
                        <div class="position-relative top-50 start-50 spinner-border text-white"></div>
                    </div>
                `);
            } else {
                $('#loader').remove();
            }
        };
    
        /**
         * Function to render flight bundles
         */
        const renderBundles = (data, isReturn) => {
            if (!Array.isArray(data) || data.length === 0) {
                return `<div class="alert alert-danger" role="alert">No flights available</div>`;
            }
    
            return data.map(row => {
                let description = parseDescription(row['description']);
                return `
                    <div class="col-auto my-4">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr class="d-flex justify-content-between">
                                            <th class="badge text-bg-primary">${row['bundledServiceName'] ?? 'N/A'}</th>
                                            <th class="badge text-bg-primary">${row['bunldedServiceId'] ?? 'N/A'}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><strong>Booking Classes</strong></td><td>${row['bookingClasses'] ?? 'N/A'}</td></tr>
                                        <tr><td colspan="2"><strong class="d-block">Description</strong></td></tr>
                                        <tr><td>Baggage:</td><td><small>${description['Baggage'] ?? 'Not Included'}</small></td></tr>
                                        <tr><td>Meal:</td><td><small>${description['Any Meal'] ?? 'Not Included'}</small></td></tr>
                                        <tr><td>Modification:</td><td><small>${description['Modification'] ?? 'Not Available'}</small></td></tr>
                                        <tr><td>Cancellation:</td><td><small>${description['Cancellation'] ?? 'Not Available'}</small></td></tr>
                                    </tbody>
                                </table>
                                <sup>TAX ${row['perPaxBundledFee'] ?? '0'}-/</sup>
                                <button type="button" class="mt-3 mb-2 btn btn-primary w-100 bookBtn" 
                                        data-is-return="${isReturn}" 
                                        data-bundle-id="${row['bunldedServiceId']}">
                                    Book Now
                                </button>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        };
    
        /** 
         * Handle booking button click 
         */
        $(document).on('click', '.bookBtn', function () {
            let bundleId = $(this).data('bundle-id');
            let isReturnBundle = $(this).data('is-return');

            if (!isReturnBundle) {
                firstBundleId = bundleId;
                if (isReturn) {
                    alert('First bundle selected. Now select a return bundle.');
                }
            } else {
                if (!firstBundleId) {
                    alert('You must select the first bundle before selecting the return bundle.');
                    return;
                }
                secondBundleId = bundleId;
            }
            if (firstBundleId && (!isReturn || secondBundleId)) {
                sendBookingRequest();
            }
        });
    
        /**
         * Function to send booking request AJAX
         */
        const sendBookingRequest = () => {
            if (!firstBundleId) {
                alert('You must select at least one bundle.');
                return;
            }
            $.post({
                url: "{{route('booking_page')}}",
                data: {
                    firstBundleId: firstBundleId ?? null,
                    secondBundleId: secondBundleId ?? null,
                    segments, paxCount, _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.redirect) {
                        window.location.href = '/demo/flights/booking';
                    } else if (response.error) {
                        alert(response.error);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        };
    
        /**
         * Function to extract flight data
         */
        const getFlightData = (data) => {
            if (!data) return null;
            return {
                departure: data['departureDateTimeLocal'],
                arrival: data['arrivalDateTimeLocal'],
                origin: data['origin'],
                destination: data['destination'],
                flightNumber: data['flightNumber']
            };
        };
    
        /**
         * Function to parse flight description
         */
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
    
        /**
         * Function to extract flight segment data
         */
        const getSegment = (data) => {
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
    </script>
</body>
</html>