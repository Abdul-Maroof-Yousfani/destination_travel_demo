@extends('home/layouts/master')
@section('title', 'Flights')
@section('style')
    <style>
        .select-flight{text-align:center;}
        .der-time ul li h2{font-size:20px;}
        .flight-card{border:1px solid #ddd;border-radius:10px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin:20px auto;}
        .flight-duration{background-color:#f2f2f2;padding:2px 8px;border-radius:5px;font-size:0.8rem;margin:5px 0;display:inline-block;}
        .price-btn{background-color:#127f9f;color:white;font-weight:bold;border:none;padding:8px 15px;border-radius:5px;display:inline-block;}
        .airline-logo{width:40px;height:auto;}
        .timesHeading{font-size:2em;font-weight:bolder;}
        .filter-dropdown-container{position:relative;display:none;}
        /* Toggle button */
        .filter-toggle{text-align:left;font-weight:600;}
        /* Dropdown hidden by default */
        .filter-dropdown{max-height:0;overflow:hidden;transition:max-height 0.5sease;border:1px solid #ddd;border-radius:10px;box-shadow:0 8px 20px rgba(0,0,0,0.15);background:#fff;margin-top:-33px;}
        /* When open */
        .filter-dropdown.open{max-height:500px;/* Adjust based on content height */
        overflow-y:auto;}
        /* Scrollbar styling */
        .filter-dropdown::-webkit-scrollbar{width:6px;}
        .filter-dropdown::-webkit-scrollbar-thumb{background-color:rgba(0,0,0,0.2);border-radius:3px;}
        /* Padding inside dropdown */
        .filter-dropdown .sho{padding:35px 15px 15px 15px;}
        .filter-dropdown-container button.filter-toggle.btn.btn-light.w-100 i{color:#00799d;}
        .support-item a span{color:#333;}
        .support-box{margin-bottom:20px;background:#fff;border-radius:12px;padding:20px 25px;box-shadow:0 4px 15px rgba(0,0,0,0.08);width:100%;border-left:5px solid #0f7d9e;font-family:'Poppins',sans-serif;}
        .support-box h4{margin-bottom:18px;font-size:20px;font-weight:600;color:#1a1a1a;}
        .support-item{display:flex;align-items:center;margin-bottom:14px;font-size:15px;color:#333;}
        .support-item i{font-size:18px;color:#0f7d9e;margin-right:10px;}
        button.btn.btn-share.mt-2{background-color:#02798b;color:#fff;border:none;padding:8px 16px;border-radius:8px;font-weight:bold;width:100%;border:2px solid #02798b;}
        button.btn.btn-share.mt-2:hover{background-color:transparent;color:#02798b;padding:8px 16px;border-radius:8px;font-weight:bold;width:100%;border:2px solid #02798b;transition:all 0.5s;}
        .flight-wrapper{margin-bottom:50px;}
        .flight-top-bar{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;background:#f5f7fc;border-radius:12px;font-family:'Poppins',sans-serif;border:1px solid #e6e9f2;}
        /* LEFT TABS */
        .left-tabs{display:flex;}
        .tab{padding:15px 40px;background:#e8edfc;font-size:15px;font-weight:500;color:#4a5570;cursor:pointer;transition:0.2s ease;}
        .tab.active{background:#00788a;color:#fff;font-weight:600;}
        /* RIGHT BUTTONS */
        .right-actions{display:flex;align-items:center;gap:18px;}
        .nonstop-btn{background:#fff;border:1px solid #d6dbea;padding:7px 16px;border-radius:8px;font-size:14px;cursor:pointer;}
        .filter-btn{background:none;border:none;color:#00788a;font-size:15px;cursor:pointer;font-weight:500;}
        .tab-content{display:none;margin-top:20px;padding:20px;background:#fff;border-radius:12px;border:1px solid #e3e7f2;}
        .tab-content.active{display:block;}
        /* filter dorpdown */
        .filter-dropdown{max-height:0;overflow:hidden;transition:max-height 0.5sease;border:none !important;border-radius:10px;box-shadow:none !important;background:transparent !important;margin-top:0px!important; position:absolute;}
        .filter-dropdown.open{max-height:500px;overflow-y:auto;position:absolute;left:-122px;top:69px;background:#fff !important;box-shadow:1px 0px 5px #000000a6 !important;z-index:1;}
        .filter-dropdown.p-3{position:absolute;left:-122px;top:63px;}
        .flight-selection{display:flex;justify-content:left;align-items:center;padding:20px;background-color:#fff;gap:20px;}
        .step{display:flex;align-items:center;}
        .step-number{font-size:16px;font-weight:bold;background-color:#bbb;color:#fff;border-radius:50%;margin-right:10px;width:35px;height:35px;text-align:center;line-height:35px;}
        .step-text{font-size:16px;font-weight:600;}
        .step-1{color:#bbb;}
        .step-2{color:#bbb;}
        .step.active{color:#007688;font-weight:600;}
        .step.active .step-number{background-color:#007789 !important;color:#fff !important;}
        .filter-dropdown-container{position:relative;display:inline-block;}
        .filter-toggle{cursor:pointer;padding:5px 10px;display:flex;align-items:center;justify-content:space-between;}
        .filter-dropdown{position:absolute;top:100%;left:0;background:#fff;border:1px solid #ccc;min-width:100%;z-index:999;display:none;}
        .filter-dropdown.open{display:block;}
        .dropdown-item{padding:5px 10px;cursor:pointer;}
        .dropdown-item:hover{background:#f0f0f0;}
        .dropdown-item.selected{font-weight:bold;background:#e0e0ff;}
    </style>
@endsection
@section('content')
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <section class="mainBanner wow fadeInLeft">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <x-search-flight />
                </div>
            </div>
        </div>
    </section>
    {{-- @dd($data) --}}
    <section class="search wow fadeInRight">
        <div class="container-fluid container-fluid-max">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="flight-selection">
                        @if ($routeType === 'MULTI')
                            @foreach($data['legs'] as $legIndex => $options)
                                @php
                                    $firstFlight = $options->first();
                                    $from = $firstFlight['departure']['code'] ?? '';
                                    $to = $firstFlight['arrival']['code'] ?? '';
                                    $date = $firstFlight['departure']['date'] ?? '';
                                @endphp
                                <div class="step step-{{ $legIndex }} {{ $loop->first ? 'active' : '' }}" id="leg-text-{{ $legIndex }}">
                                    <span class="step-number">{{ $legIndex }}</span>
                                    <span class="step-text">
                                        Leg {{ $legIndex }}: {{ $from }} → {{ $to }}
                                        <small class="text-muted d-block">{{ \Carbon\Carbon::parse($date)->format('D, d M Y') }}</small>
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <div class="step step-1 active" id="departure-text">
                                <span class="step-number">1</span>
                                <span class="step-text">Select Departing Flight ({{ $data['departure']['code'] }} - {{ $data['arrival']['code'] }})</span>
                            </div>
                            @if($data['return_count'] > 0)
                                <div class="step step-2" id="return-text">
                                    <span class="step-number">2</span>
                                    <span class="step-text">Return Flight ({{ $data['arrival']['code'] }} - {{ $data['departure']['code'] }})</span>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="flight-wrapper">
                        <div class="flight-top-bar">
                            <div class="left-tabs">
                                <div class="tab active" data-tab="suggested">Suggested</div>
                                <div class="tab" data-tab="cheapest">Cheapest</div>
                                <div class="tab" data-tab="fastest">Fastest</div>
                            </div>
                            <div class="right-actions">
                                <button class="nonstop-btn">Nonstop</button>
                                <!-- Filter Dropdown -->
                                <div class="filter-dropdown-container">
                                    <button class="filter-toggle filter-btn">
                                        <i class="fa-solid fa-filter"></i> Filters
                                    </button>
                                    <div class="filter-dropdown p-3">
                                        <div class="sho sho-mob ">
                                            <div class="shops">
                                                <h5>Stops</h5>
                                                <div class="shop-check">
                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="direct">
                                                        <label class="form-check-label" for="direct">
                                                            <strong>Direct</strong>
                                                            <br><small>None</small>
                                                        </label>
                                                    </div>

                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="one-stop">
                                                        <label class="form-check-label" for="one-stop">
                                                            <strong>1 stop</strong>
                                                            <br><small>From Rs 232,659</small>
                                                        </label>
                                                    </div>

                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="two-stops">
                                                        <label class="form-check-label" for="two-stops">
                                                            <strong>2 stops</strong>
                                                            <br><small>From Rs 214,097</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="daparture">
                                                <h5>Departure times</h5>
                                                <div class="slider-container">
                                                    <h6>Outbound</h6>
                                                    <span id="outbound-time">00:00 - 23:59</span>
                                                </div>
                                                <div class="slider-container">
                                                    <input type="range" id="outbound-start" min="0" max="1439" step="1" value="0">
                                                    <input type="range" id="outbound-end" min="0" max="1439" step="1" value="1439">
                                                </div>

                                                <div class="slider-container">
                                                    <h6 class="mt-3">Return</h6>
                                                    <span id="return-time">00:00 - 23:59</span>
                                                </div>
                                                <div class="slider-container">
                                                    <input type="range" id="return-start" min="0" max="1439" step="1" value="0">
                                                    <input type="range" id="return-end" min="0" max="1439" step="1" value="1439">
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="Journey">
                                                <h5>Journey duration</h5>
                                                <div class="slider-container">
                                                    <span id="duration-display">12.0 hours</span>
                                                </div>
                                                <input type="range" id="duration-slider" min="0" max="48" step="0.5" value="12">
                                            </div>
                                            <hr>
                                            <div class="airlines">
                                                <h5>Airlines</h5>
                                                <div class="select_clear">
                                                    <a name="" id="selectAllBtn" class="btn btn-a" href="#" role="button">Select All</a>
                                                    <a name="" id="clearAllBtn" class="btn btn-a" href="#" role="button">Clear All</a>
                                                </div>
                                                <div class="multi-box btn-group">
                                                    <ul>
                                                        <li>
                                                            <div class="select">
                                                                <input type="checkbox" id="item_1">
                                                                <label class="btn btn-warning button_select" for="item_1">Star Alliance</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="select">
                                                                <input type="checkbox" id="item_2">
                                                                <label class="btn btn-warning button_select" for="item_2">Value Alliance</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="select">
                                                                <input type="checkbox" id="item_3">
                                                                <label class="btn btn-warning button_select" for="item_3">Star Alliance</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="select">
                                                                <input type="checkbox" id="item_4">
                                                                <label class="btn btn-warning button_select" for="item_4">Value Alliance</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <div class="shop-check shop-check2">
                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="direct">
                                                        <label class="form-check-label" for="direct">
                                                            <strong>Batik Air Malaysia</strong>
                                                            <br><small>From Rs 232,659</small>
                                                        </label>
                                                    </div>

                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="one-stop">
                                                        <label class="form-check-label" for="one-stop">
                                                            <strong>Emirates</strong>
                                                            <br><small>From Rs 232,659</small>
                                                        </label>
                                                    </div>

                                                    <div class="form-check fomcheck">
                                                        <input class="form-check-input" type="checkbox" id="two-stops">
                                                        <label class="form-check-label" for="two-stops">
                                                            <strong>Etihad Airways</strong>
                                                            <br><small>From Rs 250,425</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="flightemissions">
                                                <h5>Flight emissions</h5>
                                                <div class="form-check fomcheck">
                                                    <input class="form-check-input" type="checkbox" id="two-stops">
                                                    <label class="form-check-label" for="two-stops">
                                                        <small>Only show flights with lower CO₂ emissions</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-lg-9">
                    <!-- CONTENT SECTIONS -->
                    <div class="tab-content mt-0 active" id="suggested">
                        <h3>Suggested Flights</h3>
                        @if($routeType === 'MULTI')
                            <x-multiple-flights :flightData="$data" :paxCount="$paxCount" />
                        @else
                            <x-flights :flightData="$data" :paxCount="$paxCount" />
                        @endif
                        {{-- <x-flights :flightData="$data" :paxCount="$paxCount" /> --}}
                    </div>
                    {{-- <div class="tab-content mt-0" id="cheapest">
                        <h3>Cheapest Flights</h3>
                         <x-flights :flightData="$data" :paxCount="$paxCount" />
                    </div>
                    <div class="tab-content mt-0" id="fastest">
                        <h3>Fastest Flights</h3>
                         <x-flights :flightData="$data" :paxCount="$paxCount" />
                    </div> --}}
                </div>
                <div class="col-md-12 col-lg-3 br-left">
                    <div class="support-box">
                        <h4>24/7 Customer Support</h4>
                        <div class="support-item">
                           <a href="tel:+92 3123456789"> <i class="fa fa-phone"></i>
                            <span>(021) 3123456789</span></a>
                        </div>
                        <div class="support-item">
                           <a href="tel:+92 3123456789"> <i class="fa fa-mobile"></i>
                            <span>+92 3123456789</span></a>
                        </div>
                        <div class="support-item">
                           <a href="mailto:support@travelandtour.com"> <i class="fa fa-envelope"></i>
                            <span>support@travelandtour.com</span></a>
                        </div>
                    </div>
                     <button class="btn btn-share  mt-2 "><i class="fa-regular fa-share-from-square"></i> Share</button>
                </div>
            </div>
        </div>
    </section>
    <x-session-timeout-container/>
@endsection
@section('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    localStorage.clear();
    const containers = document.querySelectorAll(".filter-dropdown-container");

    containers.forEach(container => {
        const toggle = container.querySelector(".filter-toggle");
        const dropdown = container.querySelector(".filter-dropdown");
        const items = dropdown.querySelectorAll(".dropdown-item"); // update class if needed

        // Toggle dropdown on button click
        toggle.addEventListener("click", function(e) {
            e.stopPropagation();
            
            // Close other dropdowns
            containers.forEach(c => {
                if (c !== container) {
                    c.querySelector(".filter-dropdown").classList.remove("open");
                }
            });

            // Toggle this one
            dropdown.classList.toggle("open");
        });

        // Prevent dropdown from closing when clicking inside
        dropdown.addEventListener("click", function(e) {
            e.stopPropagation();
        });

        // Close dropdown when selecting an item
        items.forEach(item => {
            item.addEventListener("click", function(e) {
                e.stopPropagation();

                // Update toggle text
                toggle.textContent = this.textContent;

                // Close dropdown
                dropdown.classList.remove("open");

                // Optional: highlight selected
                items.forEach(i => i.classList.remove("selected"));
                this.classList.add("selected");
            });
        });
    });

    // Close all dropdowns when clicking outside
    document.addEventListener("click", function() {
        containers.forEach(container => {
            const dropdown = container.querySelector(".filter-dropdown");
            dropdown.classList.remove("open");
        });
    });
});
</script>






<script>
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {

            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove("active"));

            // Add active to clicked tab
            tab.classList.add("active");

            // Hide all contents
            contents.forEach(c => c.classList.remove("active"));

            // Show related content
            let tabName = tab.getAttribute("data-tab");
            document.getElementById(tabName).classList.add("active");
        });
    });
</script>


@endsection
