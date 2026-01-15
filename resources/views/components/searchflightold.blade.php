<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
{{-- <script src="{{ URL::asset('assets/js/select2/js_tabindex.js') }}"></script> --}}
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .main-flex{display:flex;align-items:center;position:relative;margin-bottom:10px;}
    .icon-head-loc{margin-right:10px;}
    .flys{position:relative;}
    .flys input{width:100%;}
    .dropdown-list{position:absolute;top:100%;left:0;right:0;z-index:100;background:#fff;border:1px solid #ccc;max-height:200px;overflow-y:auto;display:none;}
    .dropdown-list div{padding:10px;cursor:pointer;}
    .dropdown-list div:hover{background-color:#f0f0f0;}
    .select2-container .select2-selection--single{height:50px;padding:12px 20px;border:1px solid #ddd;border-radius:6px;font-size:15px;background:white;transition:all 0.3s ease;}
    .select2-container .select2-selection--single:hover{border-color:#127F9F;box-shadow:0 0 0 1px #127F9F;}
    .select2-container .select2-selection--single:focus{outline:none;border-color:#127F9F;box-shadow:0 0 0 2px rgba(18,127,159,0.3);}
    .select2-results__option--highlighted{background-color:#127F9F !important;color:white !important;}
    .select2-container .select2-selection__arrow{height:48px;right:10px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='%23127F9F'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat center;}
    .select2-container .select2-selection__arrow b{display:none !important;}
    .select2-dropdown{border:1px solid #ddd;border-radius:6px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
    .select2-container .select2-selection--single{height:48px;padding:12px 16px;border:1px solid #e0e0e0;border-radius:8px;font-size:15px;color:#333;background-color:#fff;transition:all 0.2s ease;}
    .select2-container .select2-selection--single:hover{border-color:#127F9F;}
    .select2-container .select2-selection--single:focus{outline:none;border-color:#127F9F;box-shadow:0 0 0 2px rgba(18,127,159,0.2);}
    .select2-container .select2-selection__arrow{height:46px;right:12px;}
    .select2-container .select2-selection__arrow b{border-color:#127F9F transparent transparent transparent;border-width:6px 6px 0 6px;}
    .select2-results__option{padding:10px 16px;border-bottom:1px solid #f5f5f5;}
    .select2-results__option--highlighted{background-color:#f7f7f7 !important;color:#127F9F !important;}
    .select2-dropdown{border:1px solid #e0e0e0;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.08);}
    .select2-results__option[aria-selected=true]{background-color:#f0f0f0;color:#127F9F;}
    .calendar-container{display:flex;flex-direction:column;gap:6px;max-width:250px;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
    .calendar-container label{font-size:14px;color:#333;}
    .modern-calendar{padding:12px 16px;border:1px solid #ccc;border-radius:12px;font-size:16px;outline:none;transition:border-color 0.3s,box-shadow 0.3s;background-color:#f9f9f9;color:#333;}
    .modern-calendar:focus{border-color:#4A90E2;box-shadow:0 0 0 3px rgba(74,144,226,0.2);}
    .calendar-wrapper{display:flex;flex-direction:column;gap:8px;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;max-width:250px;}
    .calendar-wrapper label{font-size:14px;color:#333;}
    .modern-calendar{padding:12px 16px;border:2px solid #ccc;border-radius:12px;font-size:16px;outline:none;transition:border-color 0.3s ease,box-shadow 0.3s ease;background-color:#f9f9f9;color:#333;}
    .modern-calendar:focus{border-color:#4A90E2;box-shadow:0 0 0 3px rgba(74,144,226,0.2);}
    .modern-calendar::-webkit-calendar-picker-indicator{background-color:#4A90E2;border-radius:50%;padding:4px;cursor:pointer;transition:background-color 0.3s ease;}
    .modern-calendar::-webkit-calendar-picker-indicator:hover{background-color:#357ABD;}
    .icon-head-loc i{color:#30819c !important;font-size:22px;}
</style>
{{-- @if (session('error'))
    <script>
        window.onload = function () {
            let error = @json(session('error'));
            console.log(error)
            if (typeof _alert === "function") {
                _alert(error, 'error');
            } else {
                console.error("Function _alert is not defined yet.");
            }
        };
    </script>
@endif
@if (session('message'))
    <script>
        window.onload = function () {
            let message = @json(session('message'));
            if (typeof _alert === "function") {
                _alert(message, 'message');
            } else {
                console.error("Function _alert is not defined yet.");
            }
        };
    </script>
@endif --}}
<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdownMenu2');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    }
    function updateSelection(radio) {
        const dropdownToggle = document.getElementById('dropdownToggle2');
        const selectedText = dropdownToggle.querySelector(".selected-country"); // Ensure correct selection
        selectedText.textContent = radio.parentElement.textContent.trim();
        document.getElementById('dropdownMenu2').style.display = 'none'; // Close dropdown
    }
</script>
<div class="banner">
    <div class="row align-items-center">
        <div class="col-md-12 col-lg-4">
            <div class="tab-links">
                <ul class="tab-product  wow fadeInRight">
                    <li data-targetit="box-1" class="current">
                    <a class="pointer" data-toggle="tab"><i class="fa-solid fa-plane-departure"></i> Flights</a>
                    </li>
                    <li data-targetit="box-2" >
                    <a class="pointer" data-toggle="tab"><i class="fa-solid fa-globe"></i> Tours</a>
                    </li>
                    <li data-targetit="box-3" >
                    <a class="pointer" data-toggle="tab"><i class="fa-solid fa-hotel"></i> Hotels</a>
                    </li>
                    <li data-targetit="box-4" >
                    <a class="pointer" data-toggle="tab"><i class="fa-solid fa-passport"></i> Visa </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-12 col-lg-8">
            <div class="tab-head">
                <h2>Explore beautiful places in the world </h2>
            </div>
        </div>
    </div>
    <div class="box-1 showfirst  tab-content">
        <div class="radio-container">

            <div>
                <input type="radio" id="oneWaySearch" name="searchOptions" value="oneWaySearch">
                <label for="oneWaySearch">One Way</label>
            </div>
            <div>
                <input type="radio" id="returnSearch" name="searchOptions" value="returnSearch">
                <label for="returnSearch">Return</label>
            </div>
            {{-- <div>
                <input type="radio" id="connectedSearch" name="searchOptions" value="connectedSearch">
                <label for="connectedSearch">Multi City</label>
            </div> --}}
            <div class="aduls">
                <div class="dropdown-toggle" id="dropdownToggle1">
                    <span class="passengerDetails selected-country">
                        <i class="fa-solid fa-person-walking-luggage"></i> 1 Adult
                    </span>
                </div>
                <div class="dropdown-menu dropdown-menu1" id="dropdownMenu1" style="display: none;">
                    <div class="dropdown-item quantity" id="flightAdults">
                        <span>Adults</span>
                        <button class="flightDecrement">-</button>
                        <span class="count">0</span>
                        <button class="flightIncrement">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="flightChildren">
                        <span>Children</span>
                        <button class="flightDecrement">-</button>
                        <span class="count">0</span>
                        <button class="flightIncrement">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="flightInfants">
                        <span>Infants</span>
                        <button class="flightDecrement">-</button>
                        <span class="count">0</span>
                        <button class="flightIncrement">+</button>
                    </div>
                    <p id="flight-error-message" class="error-limit flightPessangerError"></p>
                </div>
            </div>
            <!-- Economy -->
            <div>
                <div class="dropdown-toggle" id="dropdownToggle2" onclick="toggleDropdown()">
                    <span class="selected-country">Economy</span>
                </div>
                <div class="dropdown-menu dropdown-menu2" id="dropdownMenu2" style="display: none;">
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="cabinClass" checked value="Y" onclick="updateSelection(this)"> Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="cabinClass" value="W" onclick="updateSelection(this)"> Premium Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="cabinClass" value="C" onclick="updateSelection(this)"> Business
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="cabinClass" value="P" onclick="updateSelection(this)"> First
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="fly">
            <ul>
                <!-- <li>
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys">
                            <input class="p-2 border-0" type="text" id="from" placeholder="Flying From (City or Airport)">
                            <div class="dropdown-list" id="fromDropdown"></div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="right-left">
                    <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys flys2">
                            <input class="p-2 border-0" type="text" id="to" placeholder="Flying To (City or Airport)">
                            <div class="dropdown-list" id="toDropdown"></div>
                        </div>
                    </div>
                </li> -->
                <li>
                    <a href="#">
                        <div class="main-flex">
                                <div class="icon-head-loc">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div class="flys">
                                    <!-- <p>Flying From (City or Airport)</p> -->
                                    <select id="from" class="select2 form-control">
                                        <option value="" disabled selected>Flying From (City or Airport)</option>
                                        <option value="RUH">Riyadh King Khālid International Airport (RUH)</option>
                                        <option value="BAH">Bahrain (BAH)</option>
                                        <option value="COK">Kochi (COK)</option>
                                        <option value="AMM">Amman (AMM)</option>
                                        <option value="SHJ">Sharjah (SHJ)</option>
                                        <option value="KHI">Karachi (KHI)</option>
                                        <option value="ISB">Islamabad (ISB)</option>
                                        <option value="LHR">Lahore (LHR)</option>
                                        <option value="JFK">New York (JFK)</option>
                                        <option value="LAX">Los Angeles (LAX)</option>
                                        <option value="ORD">Chicago (ORD)</option>
                                        <option value="MIA">Miami (MIA)</option>
                                        <option value="DFW">Dallas (DFW)</option>
                                        <option value="SFO">San Francisco (SFO)</option>
                                        <option value="ATL">Atlanta (ATL)</option>
                                        <option value="SEA">Seattle (SEA)</option>
                                        <option value="DEN">Denver (DEN)</option>
                                        <option value="BOS">Boston (BOS)</option>
                                        <option value="LAS">Las Vegas (LAS)</option>
                                        <option value="IAH">Houston (IAH)</option>
                                    </select>
                                </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="right-left mob-hid">
                        <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <a href="#">
                        <div class="main-flex">
                            
                            <div class="icon-head-loc">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="flys flys2">
                                <!-- <p>Flying To (City or Airport)</p> -->
                                <select id="to" class="select2 form-control">
                                    <option value="" disabled selected>Flying To (City or Airport)</option>
                                    <option value="RUH">Riyadh King Khālid International Airport (RUH)</option>
                                    <option value="BAH">Bahrain (BAH)</option>
                                    <option value="COK">Kochi (COK)</option>
                                    <option value="AMM">Amman (AMM)</option>
                                    <option value="SHJ">Sharjah (SHJ)</option>
                                    <option value="KHI">Karachi (KHI)</option>
                                    <option value="ISB">Islamabad (ISB)</option>
                                    <option value="LHR">Lahore (LHR)</option>
                                    <option value="JFK">New York (JFK)</option>
                                    <option value="LAX">Los Angeles (LAX)</option>
                                    <option value="ORD">Chicago (ORD)</option>
                                    <option value="MIA">Miami (MIA)</option>
                                    <option value="DFW">Dallas (DFW)</option>
                                    <option value="SFO">San Francisco (SFO)</option>
                                    <option value="ATL">Atlanta (ATL)</option>
                                    <option value="SEA">Seattle (SEA)</option>
                                    <option value="DEN">Denver (DEN)</option>
                                    <option value="BOS">Boston (BOS)</option>
                                    <option value="LAS">Las Vegas (LAS)</option>
                                    <option value="IAH">Houston (IAH)</option>
                                </select>
                            </div>
                            
                        </div>
                    </a>
                </li>


                <li>
                    <div class="main-flex">
                        
                            <div class="icon-head-loc">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                            <div class="flys flys2">
                                <div class="calendar-container calendar-wrapper ">
                                    <label for="departure">Departure Date</label>
                                    <input class="p-2 border-0 modern-calendar" type="date" id="departure" name="departure">
                                </div>
                            </div>
                       
                    </div>
                </li>
                <li>
                    <div class="main-flex">

                        
                    
                            <div class="icon-head-loc">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                            <div class="flys">


                            <div class="calendar-container calendar-wrapper">
                                    <label for="Return">Return Date</label>
                                    <input class="p-2 border-0 modern-calendar" type="date" id="returnDate">
                                </div>
                            </div>
                        
                    </div>
                </li>
                <li>
                    <div class="search-container">
                        <a class="pointer" id="searchFlight"><i class="fa-solid fa-magnifying-glass"></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-2 tab-content">

        <div class="radio-container">
            <div>
                <input type="radio" id="option1" name="options" value="option1">
                <label for="option1">One Way</label>
            </div>
            <div>
                <input type="radio" id="option2" name="options" value="option2">
                <label for="option2">Return</label>
            </div>
            <div>
                <input type="radio" id="option3" name="options" value="option3">
                <label for="option3">Multi City</label>
            </div>



            <div>
                <div class="dropdown-toggle" id="dropdownToggle1">
                    <span class="selected-country">
                        <i class="fa-solid fa-person-walking-luggage"></i> 1 Adult
                    </span>
                </div>
                <div class="dropdown-menu dropdown-menu1" id="dropdownMenu1" style="display: none;">
                    <div class="dropdown-item quantity" id="adults">
                        <span>Adults</span>
                        <button class="decrement">-</button>
                        <span class="count">1</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="children">
                        <span>Children</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="infants">
                        <span>Infants</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <p id="error-message" class="error-limit"></p>
                </div>
            </div>



            <!-- Economy -->
            <div>
                <div class="dropdown-toggle" id="dropdownToggle2" onclick="toggleDropdown()">
                    <span class="selected-country">Economy</span>
                </div>
                <div class="dropdown-menu dropdown-menu2" id="dropdownMenu2" style="display: none;">
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="economy" onclick="updateSelection(this)"> Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="premium" onclick="updateSelection(this)"> Premium Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="business" onclick="updateSelection(this)"> Business
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="first" onclick="updateSelection(this)"> First
                        </label>
                    </div>
                </div>

            </div>
        </div>
        <div class="fly">
            <ul>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys">
                            <p>Flying From (City or Airport)</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="right-left">
                    <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys flys2">
                            <p>Flying From (City or Airport)</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys flys2">
                            <p>Check in</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys">
                            <p>Check Out</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="search-container">
                    <a href="{{ route('flights')}}"><i class="fa-solid fa-magnifying-glass"></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-3  tab-content">
        <div class="radio-container">
            <div>
                <input type="radio" id="option1" name="options" value="option1">
                <label for="option1">One Way</label>
            </div>
            <div>
                <input type="radio" id="option2" name="options" value="option2">
                <label for="option2">Return</label>
            </div>
            <div>
                <input type="radio" id="option3" name="options" value="option3">
                <label for="option3">Multi City</label>
            </div>

            <div>
                <div class="dropdown-toggle" id="dropdownToggle1">
                    <span class="selected-country">
                        <i class="fa-solid fa-person-walking-luggage"></i> 1 Adult
                    </span>
                </div>
                <div class="dropdown-menu dropdown-menu1" id="dropdownMenu1" style="display: none;">
                    <div class="dropdown-item quantity" id="adults">
                        <span>Adults</span>
                        <button class="decrement">-</button>
                        <span class="count">1</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="children">
                        <span>Children</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="infants">
                        <span>Infants</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <p id="error-message" class="error-limit"></p>
                </div>
            </div>

            <!-- Economy -->
            <div>
                <div class="dropdown-toggle" id="dropdownToggle2" onclick="toggleDropdown()">
                    <span class="selected-country">Economy</span>
                </div>
                <div class="dropdown-menu dropdown-menu2" id="dropdownMenu2" style="display: none;">
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="economy" onclick="updateSelection(this)"> Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="premium" onclick="updateSelection(this)"> Premium Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="business" onclick="updateSelection(this)"> Business
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="first" onclick="updateSelection(this)"> First
                        </label>
                    </div>
                </div>

            </div>

        </div>


        <div class="fly">
            <ul>

            
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="locs">
                            <div class="icon-head-loc">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="flys">
                                <p>Flying From (City or Airport)</p>
                            </div>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="right-left mob-hid">
                    <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="locs">
                            <div class="icon-head-loc">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div class="flys flys2">
                                <p>Flying From (City or Airport)</p>
                            </div>
                        </div>
                    </div>
                    </a>
                </li>




                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys flys2">
                            <p>Check in</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys">
                            <p>Check Out</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="search-container">
                    <a href="{{ route('flights')}}"><i class="fa-solid fa-magnifying-glass"></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="box-4 tab-content">
        <div class="radio-container">
            <div>
                <input type="radio" id="option1" name="options" value="option1">
                <label for="option1">One Way</label>
            </div>
            <div>
                <input type="radio" id="option2" name="options" value="option2">
                <label for="option2">Return</label>
            </div>
            <div>
                <input type="radio" id="option3" name="options" value="option3">
                <label for="option3">Multi City</label>
            </div>



            <div>
                <div class="dropdown-toggle" id="dropdownToggle1">
                    <span class="selected-country">
                        <i class="fa-solid fa-person-walking-luggage"></i> 1 Adult
                    </span>
                </div>
                <div class="dropdown-menu dropdown-menu1" id="dropdownMenu1" style="display: none;">
                    <div class="dropdown-item quantity" id="adults">
                        <span>Adults</span>
                        <button class="decrement">-</button>
                        <span class="count">1</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="children">
                        <span>Children</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <div class="dropdown-item quantity" id="infants">
                        <span>Infants</span>
                        <button class="decrement">-</button>
                        <span class="count">0</span>
                        <button class="increment">+</button>
                    </div>
                    <p id="error-message" class="error-limit"></p>
                </div>
            </div>



            <!-- Economy -->
            <div>
                <div class="dropdown-toggle" id="dropdownToggle2" onclick="toggleDropdown()">
                    <span class="selected-country">Economy</span>
                </div>
                <div class="dropdown-menu dropdown-menu2" id="dropdownMenu2" style="display: none;">
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="economy" onclick="updateSelection(this)"> Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="premium" onclick="updateSelection(this)"> Premium Economy
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="business" onclick="updateSelection(this)"> Business
                        </label>
                    </div>
                    <div class="dropdown-item">
                        <label>
                            <input type="radio" name="class" value="first" onclick="updateSelection(this)"> First
                        </label>
                    </div>
                </div>

            </div>
        </div>
        <div class="fly">
            <ul>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys">
                            <p>Flying From (City or Airport)</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="right-left">
                    <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="flys flys2">
                            <p>Flying From (City or Airport)</p>
                        </div>
                    </div>
                    </a>
                </li>


                



                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys flys2">
                            <p>Check in</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                    <div class="main-flex">
                        <div class="icon-head-loc">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <div class="flys">
                            <p>Check Out</p>
                        </div>
                    </div>
                    </a>
                </li>
                <li>
                    <div class="search-container">
                    <a href="{{ route('flights')}}"><i class="fa-solid fa-magnifying-glass"></i></a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('dropdownToggle1').addEventListener('click', function(event) {
        event.stopPropagation(); // Prevent immediate closing
        const menu = document.getElementById('dropdownMenu1');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
    $(document).ready(function () {
        const getURLParam = param => new URLSearchParams(window.location.search).get(param) || "";
        
        let departure = $("#departure");
        let returnDate = $("#returnDate");

        let today = new Date().toISOString().split('T')[0];
        departure.attr("min", today)

        departure.val(getURLParam("dep"));
        returnDate.val(getURLParam("return"));

        returnDate.attr("min", departure.val() || today)
        departure.on("change", function () {
            let selectedDeparture = $(this).val();
            returnDate.attr("min", selectedDeparture);

            // clear returnDate if it's before the new departure date
            if (returnDate.val() < selectedDeparture) {
                returnDate.val("");
            }
        });
        $("#from").val(getURLParam("arr"));
        $("#to").val(getURLParam("dest"));

        if(!$("#returnDate").val()){
            $("#returnDate").prop("disabled", true);
            $("#oneWaySearch").prop("checked", true);
        } else {
            $("#returnSearch").prop("checked", true);
        }

        $('#oneWaySearch').change(function() {
            $("#returnDate").prop("disabled", this.checked);
            $("#returnDate").val(null);
        });
        $('#returnSearch').change(function() {
            $("#returnDate").removeProp("disabled", this.checked);
            $("#returnDate").val(getURLParam("return"));
        });

        let adults = parseInt(getURLParam("adt")) || 1;
        let children = parseInt(getURLParam("chd")) || 0;
        let infants = parseInt(getURLParam("inf")) || 0;

        $("#flightAdults .count").text(adults);
        $("#flightChildren .count").text(children);
        $("#flightInfants .count").text(infants);

        const updatePassengerSummary = () => {
            let totalPassengers = adults + children + infants;
            $(".passengerDetails").html(`<i class="fa-solid fa-person-walking-luggage"></i> ${totalPassengers} Passenger${totalPassengers > 1 ? "s" : ""}`);
        };

        const validatePassengerCounts = () => {
            let totalPassengers = adults + children + infants;
            let errorMsg = "";

            if (infants > adults) {
                errorMsg = "Infants cannot exceed the number of adults.";
            } else if (totalPassengers > 9) {
                errorMsg = "Total passengers cannot be more than 9.";
            }
            $(".flightPessangerError").text(errorMsg);
            return errorMsg === "";
        };

        $(".flightIncrement, .flightDecrement").click(function () {
            let parent = $(this).closest(".quantity");
            let countSpan = parent.find(".count");
            let isIncrement = $(this).hasClass("flightIncrement");

            let totalPassengers = adults + children + infants;

            if (isIncrement) {
                if (totalPassengers >= 9) {
                    $(".flightPessangerError").text("Total passengers cannot be more than 9.");
                    return;
                }

                if (parent.attr("id") === "flightAdults") adults++;
                else if (parent.attr("id") === "flightChildren") children++;
                else if (parent.attr("id") === "flightInfants") {
                    if (infants < adults) infants++;
                    else {
                        $(".flightPessangerError").text("Infants cannot exceed the number of adults.");
                        return;
                    }
                }
            } else {
                if (parent.attr("id") === "flightAdults") adults = Math.max(adults - 1, 1);
                else if (parent.attr("id") === "flightChildren") children = Math.max(children - 1, 0);
                else if (parent.attr("id") === "flightInfants") infants = Math.max(infants - 1, 0);
            }

            $("#flightAdults .count").text(adults);
            $("#flightChildren .count").text(children);
            $("#flightInfants .count").text(infants);

            validatePassengerCounts();
            updatePassengerSummary();
        });

        updatePassengerSummary();

        $("#searchFlight").click(function (event) {
            event.preventDefault();
            
            let cabinClass = $('input[name="cabinClass"]:checked').val();
            let from = $('#from').val();
            let destination = $('#to').val();
            let departure = $("#departure").val();
            let returnDate = $("#returnDate").val();

            if (!from || !destination || !departure) return _alert("Please fill all required fields.", 'warning')

            if (!validatePassengerCounts()) return;

            window.location.href = `/flights?arr=${from}&dest=${destination}&dep=${departure}&return=${returnDate}&cabinClass=${cabinClass}&adt=${adults}&chd=${children}&inf=${infants}`;
        });
    });
</script>
<!-- citys -->
<script>
    $(document).ready(function() {
        $('.select2').select2(); // Initialize Select2 for all elements with the class 'select2'
    });




    $(document).ready(function () {
        // Initialize select2
        $('.select2').select2();

        // Jab "from" city select ho to "to" field open ho
        $('#from').on('select2:select', function () {
            $('#to').select2('open');
        });

        // Jab "to" city select ho to "departure" par focus ho
        $('#to').on('select2:select', function () {
            $('#departure').focus();
        });

        // Jab "departure" date select ho to "returnDate" par focus ho
        $('#departure').on('change', function () {
            $('#returnDate').focus();
        });
    });

    jQuery(document).ready(function($) {
        var docBody = $(document.body);
        var shiftPressed = false;
        var clickedOutside = false;
        //var keyPressed = 0;

        docBody.on('keydown', function(e) {
            var keyCaptured = (e.keyCode ? e.keyCode : e.which);
            //shiftPressed = keyCaptured == 16 ? true : false;
            if (keyCaptured == 16) { shiftPressed = true; }
        });
        docBody.on('keyup', function(e) {
            var keyCaptured = (e.keyCode ? e.keyCode : e.which);
            //shiftPressed = keyCaptured == 16 ? true : false;
            if (keyCaptured == 16) { shiftPressed = false; }
        });

        docBody.on('mousedown', function(e){
            // remove other focused references
            clickedOutside = false;
            // record focus
            if ($(e.target).is('[class*="select2"]')!=true) {
                clickedOutside = true;
            }
        });

        docBody.on('select2:opening', function(e) {
            // this element has focus, remove other flags
            clickedOutside = false;
            // flag this Select2 as open
            $(e.target).attr('data-s2open', 1);
        });
        docBody.on('select2:closing', function(e) {
            // remove flag as Select2 is now closed
            $(e.target).removeAttr('data-s2open');
        });

        docBody.on('select2:close', function(e) {

            var elSelect = $(e.target);
            elSelect.removeAttr('data-s2open');
            var currentForm = elSelect.closest('form');
        
            var othersOpen = currentForm.has('[data-s2open]').length;
            if (othersOpen == 0 && clickedOutside==false) {
                /* Find all inputs on the current form that would normally not be focus`able:
                *  - includes hidden <select> elements whose parents are visible (Select2)
                *  - EXCLUDES hidden <input>, hidden <button>, and hidden <textarea> elements
                *  - EXCLUDES disabled inputs
                *  - EXCLUDES read-only inputs
                */
                var inputs = currentForm.find(':input:enabled:not([readonly], input:hidden, button:hidden, textarea:hidden)')
                    .not(function () {   // do not include inputs with hidden parents
                        return $(this).parent().is(':hidden');
                    });
                var elFocus = null;
                $.each(inputs, function (index) {
                    var elInput = $(this);

                    if (elInput.attr('id') == elSelect.attr('id')) {
                        if ( shiftPressed) { // Shift+Tab
                            elFocus = inputs.eq(index - 1);

                        } else {
                            elFocus = inputs.eq(index + 1);

                        }
                        return false;
                    }
                });
                if (elFocus !== null) {
                    // automatically move focus to the next field on the form
                    var isSelect2 = elFocus.siblings('.select2').length > 0;
                    if (isSelect2) {
                        elFocus.select2('open');
                    } else {
                        elFocus.focus();
                    }
                }
            }
        });

        docBody.on('focus', '.select2', function(e) {

            var elSelect = $(this).siblings('select');
            if (elSelect.is('[disabled]')==false && elSelect.is('[data-s2open]')==false
                && $(this).has('.select2-selection--single').length>0) {
                elSelect.attr('data-s2open', 1);
                elSelect.select2('open');
            }
        });

    });
</script>