@extends('home/layouts/master')

@section('title', 'View booking')
@section('style')
    <style>
        .booking-container{max-width:900px;margin:auto;}
        .back-ground{background:#fff;border-radius:8px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
        strong{font-weight:bold;}
        .view-bookings{padding:50px 0;}
        .view-bookings2{padding:10px 0px 50px 0px;}
        .view-bookings3{padding:50px 0px 10px 0px;}
        .header{display:flex;justify-content:space-between;align-items:center;padding:10px 0px;text-align:left;}
        .header div{font-size:14px;color:#333;}
        .btn{background:#127F9F;color:#fff;border:1px solid #127F9F;font-weight:600;padding:8px 14px;border-radius:4px;cursor:pointer;font-size:14px;}
        .btn:hover{background:#0056b300;color:#127F9F;border:1px solid #127F9F;}
        .flight-box-depart{display:flex;justify-content:space-between;align-items:center;background:#ededed;border-radius:6px 6px 0px 0px;padding:8px 20px;}
        .flight-box{display:flex;justify-content:space-between;align-items:center;}
        .flight-info{display:flex;flex-direction:column;}
        .flight-info h3{margin:0;font-size:16px;font-weight:bold;}
        .flight-info span{font-size:14px;color:#555;}
        .flight-time{text-align:center;}
        .flight-time h2{margin:0;font-size:18px;font-weight:bold;}
        .flight-time span{font-size:13px;color:#666;}
        .flight-price{font-size:16px;padding:40px 0px 40px 20px;border-left:1px solid #ddd;}
        .flight-price h3{margin-bottom:5px;color:#474747;}
        .weight i{color:#474747;}
        .weight span{color:#474747;}
        .summary{margin-top:20px;}
        .travelers{margin-top:20px;}
        .travelers p{margin-bottom:20px;}
        .summary-row{display:flex;justify-content:space-between;font-size:14px;color:#333;margin-bottom:20px;}
        .summary-row.total{font-weight:bold;font-size:16px;color:#127F9F;margin:0;padding:30px 0px 20px 0px;border-top:1px solid #ddd;margin-top:30px;}
        .travelers-row.total p{margin:0;padding:4px 0px;color:#333;}
        .travelers-row.total p i{color:#333;}
        .travelers-row.total{border-top:1px solid #ddd;padding:30px 0px 20px 0px;}
        .times{display:flex;justify-content:space-between;margin-bottom:10px;}
        .times h2{font-size:26px;padding:0px 15px;}
        .times span{font-size:13px;color:#0e0e0e;background:#ddd;line-height:25px;padding:0px 6px;}
        .view-head h3{font-size:30px;font-weight:bold;margin-bottom:25px;}
        .flight-head h3{font-size:20px;font-weight:bold;margin-bottom:25px}
        .header-item h5{margin-bottom:5px}
        .header-payment h5{margin-bottom:5px}
        .header-pay p{color:#127F9F;font-weight:600;}
        .biniti{background:#fdfdfd;padding:5px 5px;border:1px solid #ddd;border-radius:5px;text-align:center;}
        .biniti i{color:#127F9F;}
        .complated{margin-top:20px;}
        .complated button.btn.btn-sm.btn_secondary{width:100%;padding:15px 0px;}
        .flight-box-depart h4{font-size:18px;font-weight:600;padding:10px 0px;}
        .flight-box-depart h3{font-weight:700;}
        .flight-details{padding:20px 0;}
    </style>
@endsection
@section('content')
{{-- @dd($booking) --}}

<div class="container">
    <div class="row">
        <div class="col-md-12 col-lg-8">
            <section class="view-bookings">
                <div class="booking-container">
                    <div class="view-head">
                        <h3>View Booking</h3>
                    </div>
                    <div class="back-ground">
                        <!-- Header Section -->
                        <div class="header">
                            <div class="header-item">
                                <h5>Order ID</h5>
                                <p><strong>{{ $booking->order_id ?? '' }}</strong></p> 
                            </div>
                            <div class="header-item">
                                <h5>Booking Date</h5>
                                <p><strong>{{ $booking->created_at->format('D, d M Y, h:i A') ?? '' }}</strong></p>
                            </div>
                            <div class="header-payment">
                                <h5>Payment Method</h5>
                                <button class="btn btn-sm btn_secondary">Complete Payment</button>
                            </div>
                            <div class="header-pay">
                                <p><small>
                                    @if($booking->status === 'issued' || $booking->status === 'cancel')
    
                                    @elseif($booking->status === 'expired' || !$booking->payment_limit || now()->greaterThan($booking->payment_limit))
                                        Expired
                                    @else
                                        Pay before (<span id="countdown"></span>)
                                    @endif
                                </small></p>
                                <div class="biniti"><i class="fa-solid fa-circle-info"></i> {{ strtoupper($booking->status ?? '') }}</div>
                            </div>
                        </div>
                    </div>
                <div>
            </section>
            <section class="view-bookings2">
                <div class="booking-container">
                    <div class="flight-head">
                        <h3>Flights Selected</h3>
                    </div>
                    @forelse ($booking->flights as $key => $flight)
                        <div class="flight-details">
                            <div class="flight-box-depart">
                                <div class="div">
                                    <h3>{{ $key === 0 ? 'Departure' : 'Return' }}</h3>
                                </div>
                                <div class="div">
                                    <h3>{{ $flight->departure_date->format('d M, Y') ?? '' }}</h3>
                                </div>
                            </div>
                            <div class="back-ground">
                                <!-- Flight Section -->
                                <div class="flight-box">
                                    <div class="flight-info">
                                        <img src="{{ asset('assets/images/logos/' . strtolower($booking->airline ?? '') . '.png') }}" alt="{{ $flight->airline ?? '' }} Logo" style="width:50px;height:auto;">
                                        <br>
                                        <h3>{{ $flight->airline ?? '' }}</h3>
                                    </div>
                                    <div class="flight-time">
                                        <div class="times">
                                            <h2>{{ $flight->departure_date->format('h:i A') ?? '' }}</h2>
                                            <span>{{ $flight->duration ?? '' }}</span>
                                            <h2>{{ $flight->arrival_date->format('h:i A') ?? '' }}</h2>
                                        </div>
                                        <div class="city">
                                            <span> {{ $flight->departure_name ?? '' }} ({{ $flight->departure_code ?? '' }}) - {{ $flight->is_connected ? 'Connecting' : 'Nonstop' }} - {{ $flight->arrival_name ?? '' }} ({{ $flight->arrival_code ?? '' }})</span>
                                        </div>
                                    </div>
                                    <div class="weight">
                                        <span><i class="fa-solid fa-chair"></i> {{ $flight->cabin_name_with_code ?? '' }}</span>
                                    </div>
                                    <div class="flight-price">
                                        <h3>Total Price</h3>
                                        <p><strong> {{ $flight->price_with_code ?? '' }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p>No flights found.</p>
                    @endforelse
                </div>
            </section>
        </div>
        <div class="col-md-12 col-lg-4">
            <section class="view-bookings3">
                <div class="booking-container">
                    <div class="flight-box-depart">
                        <div class="flight-head">
                             <h4>Price Summary</h4>
                        </div>
                    </div>
                    <div class="back-ground">
                        <!-- Price Summary -->
                        <div class="summary">
                           @forelse ($booking->bookingItems as $item)
                               <div class="summary-row">
                                   <span>{{ $booking->airline ?? '' }} Airline - {{ $item->passenger_code }}</span>
                                   <span>{{ $item->total_price ?? '' }}</span>
                               </div>
                           @empty
                               
                           @endforelse
                            <div class="summary-row">
                                <span>Tax</span>
                                <span>{{ $booking->tax_code ?? 'PKR' }} {{ number_format($booking->tax ?? 0, 2) }}</span>
                            </div>
                            <div class="summary-row total">
                                <span>Price you pay</span>
                                <span>{{ $booking->total_tax_price ?? '' }}</span>
                            </div>
                        </div>
                     </div>
                </div>
            </section>
            <section class="view-bookings2">
                <div class="booking-container">
                    <div class="flight-box-depart">
                        <div class="flight-head">
                             <h4>Travelers</h4>
                        </div>
                    </div>
                    @php
                        $passengers = is_string($booking->passenger_details) ? (json_decode($booking->passenger_details, true) ?? []) : $booking->passenger_details;
                    @endphp
                    <div class="back-ground">
                        <!-- Traveler Info -->
                        @forelse ($passengers as $passenger)
                            <div class="travelers">
                                <div class="travelers-row">
                                <p><strong><i class="fa-solid fa-user"></i> {{ $passenger['title'] ?? 'MR' }}. {{ $passenger['given_name'] ?? '' }} {{ $passenger['surname'] ?? '' }}</strong></p>
                                </div>
                                <div class="travelers-row total">
                                    <p><i class="fa fa-solid fa-mobile"></i> {{ $booking->client->full_phone ?? '' }}</p>
                                    <p><i class="fa-solid fa-envelope"></i> {{ $booking->client->email ?? '' }}</p>
                                </div>
                            </div>
                        @empty
                            <p>No travelers found.</p>
                        @endforelse
                    </div>  
                    <div class="complated">
                        <button class="btn btn-sm btn_secondary">Complete Payment</button>
                    </div>
                </div>
            </section>
        </div>
    </div>

</div>
@endsection
@section('script')
@if($booking->status !== 'expired' || $booking->payment_limit || now()->greaterThan($booking->payment_limit))
    <script>
        $(function() {
            let paymentLimit = new Date("{{ $booking->payment_limit }}").getTime();
            let serverNow    = new Date("{{ now() }}").getTime();

            let remaining = Math.floor((paymentLimit - serverNow) / 1000);

            function updateCountdown() {
                if (remaining <= 0) {
                    $("#countdown").text("Expired");
                    clearInterval(timer);
                    return;
                }

                let hours   = Math.floor((remaining % (60*60*24)) / 3600);
                let minutes = Math.floor((remaining % 3600) / 60);
                let seconds = remaining % 60;

                hours   = hours.toString().padStart(2, '0');
                minutes = minutes.toString().padStart(2, '0');
                seconds = seconds.toString().padStart(2, '0');

                $("#countdown").text(hours + ":" + minutes + ":" + seconds);

                remaining--;
            }

            updateCountdown();
            let timer = setInterval(updateCountdown, 1000);
        });
    </script>
@endif
@endsection