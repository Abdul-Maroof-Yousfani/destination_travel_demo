{{-- @dd('okok') --}}
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome & Select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
   .flys{position:relative;}
.flys input{width:100%;}
.dropdown-list{position:absolute;top:100%;left:0;right:0;z-index:100;background:#fff;border:1px solid #ccc;max-height:200px;overflow-y:auto;display:none;}
.dropdown-list div{padding:10px;cursor:pointer;}
.dropdown-list div:hover{background-color:#f0f0f0;}
.modern-calendar{padding:12px 16px;border:1px solid #ccc;border-radius:12px;font-size:16px;outline:none;transition:border-color 0.3s,box-shadow 0.3s;background-color:#f9f9f9;color:#333;}
.modern-calendar:focus{border-color:#4A90E2;box-shadow:0 0 0 3px rgba(74,144,226,0.2);}
.modern-calendar{padding:12px 16px;border:2px solid #ccc;border-radius:12px;font-size:16px;outline:none;transition:border-color 0.3s ease,box-shadow 0.3s ease;background-color:#f9f9f9;color:#333;}
.modern-calendar:focus{border-color:#4A90E2;box-shadow:0 0 0 3px rgba(74,144,226,0.2);}
.modern-calendar::-webkit-calendar-picker-indicator{background-color:#4A90E2;border-radius:50%;padding:4px;cursor:pointer;transition:background-color 0.3s ease;}
.modern-calendar::-webkit-calendar-picker-indicator:hover{background-color:#357ABD;}
.booking-section{background:#fff;border-radius:15px;padding:25px;box-shadow:0 4px 15px rgba(0,0,0,.1);}
.nav-tabs .nav-link.active{background:#0d6efd;color:#fff !important;border:none;}
.nav-tabs .nav-link{color:#0d6efd;font-weight:500;}
.search-btn{background:#0d6efd;color:#fff;border-radius:10px;font-weight:500;}
.search-btn:hover{background:#0b5ed7;}
.input-group-text{background-color:#fff;cursor:pointer;transition:all 0.3s ease;color:#00839d;}
.input-group-text:hover{color:#000;}
.form-control{height:48px;padding:12px 16px;border:1px solid #e0e0e0;border-radius:8px;font-size:15px;color:#333;background-color:#fff !important;transition:all 0.2sease;}
/* Only target dropdowns inside .dropdowns */
 /* .dropdowns .dropdown{position:relative;}
.dropdowns .dropdown-toggle{cursor:pointer;background:#fff;}
.dropdowns .dropdown-menu{display:none;position:absolute;background:#fff;padding:12px;border:1px solid #ddd;width:220px;border-radius:6px;z-index:9999;}
.dropdowns .dropdown-menu.show{display:block !important;}
.dropdowns .quantity{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.dropdowns .quantity button{width:26px;height:26px;border:1px solid #ccc;background:#f7f7f7;cursor:pointer;}
.dropdowns .quantity button:hover{background:#e8e8e8;}
*/
 /* Optional:clean inputs */
 /* .dropdowns input[type="radio"]{margin-right:6px;}
*/
 /* WRAPPER (Only inside .dropdowns) */
 .dropdowns .dropdown{position:relative;cursor:pointer;}
/* Toggle Button */
 .dropdowns .dropdown-toggle{padding:10px 15px;border:1px solid #ddd;border-radius:6px;background:#fff;display:flex;align-items:center;gap:8px;}
/* Dropdown Menu */
 .dropdowns .dropdown-menu{position:absolute;top:110%;left:0;min-width:180px;background:#fff;border:1px solid #ddd;border-radius:6px;padding:10px;display:none;z-index:9999;}
/* Show class toggled by JS */
 .dropdowns .dropdown-menu.show{display:block;}
/* Dropdown item style */
 .dropdowns .dropdown-item{padding:8px 5px;font-size:14px;cursor:pointer;border-radius:4px;}
.dropdowns .dropdown-item:hover{background:#f5f5f5;}
/* Passenger Buttons UI (safe,minimal) */
 /* .dropdowns .quantity{display:flex;justify-content:space-between;align-items:center;padding:6px 0;}
.dropdowns .quantity button{width:26px;height:26px;border:1px solid #ccc;background:#fff;cursor:pointer;border-radius:4px;}
.dropdowns .quantity .count{min-width:20px;text-align:center;font-weight:600;}
*/
 .dropdowns .quantity{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;}
.dropdowns .quantity button{width:26px;height:26px;border:1px solid #ccc;background:#f7f7f7;cursor:pointer;}
.dropdowns .quantity button:hover{background:#e8e8e8;}
.dropdowns .error-limit{color:red;font-size:12px;padding-top:5px;}
.quantity button:hover{color:#000 !important;}
.dropdowns .dropdown-menu{display:none !important;}
.dropdowns .dropdown-menu.show{display:block !important;}
/* Multi City Styles */
 .multi-city-form{margin-top:20px;}
.multi-city-segments-container{max-height:500px;overflow-y:auto;overflow-x:hidden;padding-right:10px;margin-bottom:20px;}
.multi-city-segments-container::-webkit-scrollbar{width:8px;}
.multi-city-segments-container::-webkit-scrollbar-track{background:#f1f1f1;border-radius:10px;}
.multi-city-segments-container::-webkit-scrollbar-thumb{background:#888;border-radius:10px;}
.multi-city-segments-container::-webkit-scrollbar-thumb:hover{background:#555;}
.multi-city-segment{margin-bottom:20px;position:relative;}
.multi-city-segment-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding:0 5px;}
.multi-city-segment-title{font-weight:600;color:#333;font-size:14px;}
.remove-segment-btn{background:#dc3545;color:#fff;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px;display:flex;align-items:center;gap:4px;}
.remove-segment-btn:hover{background:#c82333;}
.multi-city-segment .fly{margin-bottom:0;}
.multi-city-actions{display:flex;justify-content:space-between;align-items:center;margin-top:15px;padding-top:15px;border-top:1px solid #e0e0e0;}
.add-segment-btn{background: #02798b;color:#fff;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;display:flex;align-items:center;gap:8px;transition:background 0.3s ease;}
.add-segment-btn:hover{background:#000;}
@media (max-width:768px){.multi-city-actions{flex-direction:column;gap:10px;}
.add-segment-btn{width:100%;justify-content:center;}
}

</style>
@if (session('error'))
    <script>
        window.onload = function() {
            let error = @json(session('error'));
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
        window.onload = function() {
            let message = @json(session('message'));
            if (typeof _alert === "function") {
                _alert(message, 'message');
            } else {
                console.error("Function _alert is not defined yet.");
            }
        };
    </script>
@endif
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
        <div class="col-md-12 col-lg-5">
            <div class="tab-links">
                <ul class="tab-product  wow fadeInRight">
                    <li data-targetit="box-1" class="current">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-plane-departure"></i> Flights</a>
                    </li>
                    <li data-targetit="box-2">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-globe"></i> Tours</a>
                    </li>
                    <li data-targetit="box-3">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-hotel"></i> Hotels</a>
                    </li>
                    <li data-targetit="box-4">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-passport"></i> Visa </a>
                    </li>
                </ul>
            </div>
            <div class="tab-links tab-links-mob">
                <ul class="tab-product  wow fadeInRight">
                    <li data-targetit="box-1" class="current">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-plane-departure"></i></a>
                    </li>
                    <li data-targetit="box-2">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-globe"></i></a>
                    </li>
                    <li data-targetit="box-3">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-hotel"></i></a>
                    </li>
                    <li data-targetit="box-4">
                        <a class="pointer" data-toggle="tab"><i class="fa-solid fa-passport"></i></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-12 col-lg-7">
            <div class="tab-head">
                <h2>Explore beautiful places in the world </h2>
            </div>
        </div>
    </div>
    <div class="box-1 showfirst  tab-content">
        <div class="radio-container">
            <div>
                <input type="radio" id="oneWaySearch" name="searchOptions" value="ONEWAY" checked>
                <label for="oneWaySearch">One Way</label>
            </div>
            <div>
                <input type="radio" id="returnSearch" name="searchOptions" value="ROUND">
                <label for="returnSearch">Return</label>
            </div>
            <div>
                <input type="radio" id="multiCitySearch" name="searchOptions" value="MULTI">
                <label for="multiCitySearch">Multi City</label>
            </div>
            {{-- <div>
                <input type="radio" id="connectedSearch" name="searchOptions" value="connectedSearch">
                <label for="connectedSearch">Multi City</label>
            </div> --}}

            <div class="dropdowns" style="display: flex; gap: 15px;">
                <!-- Adults -->
                <div class="dropdown">
                    <div class="dropdown-toggle" id="dropdownToggle1">
                        <span class="passengerDetails"><i class="fa-solid fa-person-walking-luggage"></i> 1 Adult</span>
                        <!-- <i class="fa-solid fa-chevron-down"></i> -->
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu1">
                        <div class="dropdown-item quantity" id="flightAdults">
                            <span>Adults</span>
                            <div>
                                <button class="flightDecrement">-</button>
                                <span class="count">1</span>
                                <button class="flightIncrement">+</button>
                            </div>
                        </div>
                        <div class="dropdown-item quantity" id="flightChildren">
                            <span>Children</span>
                            <div>
                                <button class="flightDecrement">-</button>
                                <span class="count">0</span>
                                <button class="flightIncrement">+</button>
                            </div>
                        </div>
                        <div class="dropdown-item quantity" id="flightInfants">
                            <span>Infants</span>
                            <div>
                                <button class="flightDecrement">-</button>
                                <span class="count">0</span>
                                <button class="flightIncrement">+</button>
                            </div>
                        </div>
                        <p id="flight-error-message" class="error-limit flightPessangerError"></p>
                    </div>
                </div>

                <!-- Economy -->
                <div class="dropdown">
                    <div class="dropdown-toggle" id="dropdownToggle2">
                        <span class="selected-country">Economy</span>
                    </div>
                    <div class="dropdown-menu economy-menu" id="dropdownMenu2">
                        <div class="dropdown-item">
                            <label>
                                <input type="radio" name="cabinClass" checked value="Y"
                                    onclick="updateSelection(this)"> Economy
                            </label>
                        </div>
                        <div class="dropdown-item">
                            <label>
                                <input type="radio" name="cabinClass" value="W"
                                    onclick="updateSelection(this)"> Premium Economy
                            </label>
                        </div>
                        <div class="dropdown-item">
                            <label>
                                <input type="radio" name="cabinClass" value="C"
                                    onclick="updateSelection(this)"> Business
                            </label>
                        </div>
                        <div class="dropdown-item">
                            <label>
                                <input type="radio" name="cabinClass" value="P"
                                    onclick="updateSelection(this)"> First
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- One Way / Return Search Form -->
        <div class="fly" id="standardSearchForm">
            <ul>
                <li>
                    <a href="#">
                        <div class="select2-icon-wrapper">
                            <i class="fa-solid fa-location-dot select2-inner-icon"></i>
                            <select id="from" class=" select2"
                                data-placeholder="Select Departure City"></select>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="mob-hid">
                        <i class="fa-solid fa-right-left"></i>
                    </div>
                </li>
                <li>
                    <a href="#">
                        <div class="select2-icon-wrapper">
                            <i class="fa-solid fa-location-dot select2-inner-icon"></i>
                            <select id="to" class="form-control select2"
                                data-placeholder="Flying To (City or Airport)"></select>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="calendar-container flys">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                            <input id="departure" name="departure" type="text" class="form-control"
                                placeholder="Departure Date">
                        </div>
                    </div>
                </li>
                <li>
                    <div class="calendar-container flys">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                            <input id="returnDate" name="return" type="text" class="form-control"
                                placeholder="Return Date">
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

        <!-- Multi City Search Form -->
        <div class="multi-city-form" id="multiCitySearchForm" style="display: none;">
            <div class="multi-city-segments-container" id="multiCitySegments">
                <!-- Segments will be added dynamically here -->
            </div>
            <div class="multi-city-actions">
                <button type="button" class="add-segment-btn" id="addSegmentBtn">
                    <i class="fa-solid fa-plus"></i> Add Another Flight
                </button>
                <div class="search-container">
                    <a class="pointer" id="searchMultiCityFlight">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="box-2 tab-content"></div>-->
</div>
<script>
    /* ==========================
    DROPDOWN MENU HANDLER
    ========================== */
    document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const menu = toggle.nextElementSibling;
            document.querySelectorAll('.dropdown-menu').forEach((m) => {
                if (m !== menu) m.classList.remove('active');
            });
            menu.classList.toggle('active');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu').forEach((m) => m.classList.remove('active'));
    });
</script>

<script>
    /* ==========================
    SELECT2 INITIALIZATION
    ========================== */
    $(document).ready(function() {
        $(".select2").select2({
            theme: "default",
            placeholder: function() {
                return $(this).data("placeholder");
            },
            minimumResultsForSearch: 5,
            width: "100%",
        });
    });
</script>

<script>
    /* ==========================
    FLATPICKR INITIALIZATION
    ========================== */
    const returnPicker = flatpickr("#returnDate", {
        dateFormat: "d M Y",
        minDate: "today"
    });

    flatpickr("#departure", {
        dateFormat: "d M Y",
        minDate: "today",
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                returnPicker.set('minDate', selectedDates[0]);
            }
        }
    });

    flatpickr("#tourStart", {
        dateFormat: "d M Y",
        minDate: "today"
    });
    flatpickr("#tourEnd", {
        dateFormat: "d M Y",
        minDate: "today"
    });
    flatpickr("#checkIn", {
        dateFormat: "d M Y",
        minDate: "today"
    });
    flatpickr("#checkOut", {
        dateFormat: "d M Y",
        minDate: "today"
    });
    flatpickr("#visaDate", {
        dateFormat: "d M Y",
        minDate: "today"
    });
</script>

<script>
    /* ==========================
    INPUT FOCUS ON ICON CLICK
    ========================== */
    document.querySelectorAll('.input-group-text').forEach(icon => {
        icon.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input) input.focus();
        });
    });
</script>

<script>
    /* ==========================
    AIRPORT SELECT2 WITH STATIC + AJAX
    ========================== */
    const staticAirports = [
        @foreach ($airports as $airport)
            {
                id: '{{ $airport->code }}',
                text: '{{ addslashes($airport->name) }} ({{ $airport->code }})'
            },
        @endforeach
    ];

    function setupAirportSelect(selector) {
        $(selector).select2({
            theme: 'classic',
            placeholder: $(selector).data('placeholder'),
            minimumInputLength: 0,
            ajax: {
                transport: function(params, success, failure) {
                    const term = params.data.term || '';

                    if (!term.length) {
                        success({
                            results: staticAirports
                        });
                        return;
                    }

                    $.ajax({
                        url: '{{ route('airport') }}',
                        dataType: 'json',
                        delay: 250,
                        data: {
                            term
                        },
                        success: function(data) {
                            success({
                                results: data.results
                            });
                        },
                        error: failure
                    });
                },
                processResults: function(data) {
                    return data;
                },
                cache: true
            }
        });
    }

    function setInitialAirportValue(selector, code) {
        if (!code) return;

        const staticMatch = staticAirports.find(a => a.id === code);

        if (staticMatch) {
            const option = new Option(staticMatch.text, staticMatch.id, true, true);
            $(selector).append(option).trigger('change');
        } else {
            $.ajax({
                url: '{{ route('airport') }}',
                data: {
                    term: code
                },
                dataType: 'json',
                success: function(data) {
                    const match = data.results.find(item => item.id === code);
                    if (match) {
                        const option = new Option(match.text, match.id, true, true);
                        $(selector).append(option).trigger('change');
                    }
                }
            });
        }
    }

    /* ==========================
    FLIGHT SEARCH LOGIC
    ========================== */
    $(document).ready(function() {
        const getURLParam = (param) => new URLSearchParams(window.location.search).get(param) || "";
        const departurePicker = flatpickr("#departure", {
            dateFormat: "d M Y",
            minDate: "today",
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    returnPicker.set('minDate', selectedDates[0]);
                }
            }
        });

        const returnPicker = flatpickr("#returnDate", {
            dateFormat: "d M Y",
            minDate: "today"
        });

        // Helper to parse ISO to Flatpickr format
        function setPickerFromISO(picker, isoDate) {
            if (!isoDate) return;
            const date = new Date(isoDate);
            if (!isNaN(date)) {
                picker.setDate(date, true); // true triggers onChange
            }
        }

        // Set dates from URL params
        setPickerFromISO(departurePicker, getURLParam("dep"));
        setPickerFromISO(returnPicker, getURLParam("return"));
        //   let departure = $("#departure");
        //   let returnDate = $("#returnDate");

        //   let today = new Date().toISOString().split('T')[0];
        //   departure.attr("min", today);

        //   departure.val(getURLParam("dep"));
        //   returnDate.val(getURLParam("return"));

        //   returnDate.attr("min", departure.val() || today);

        //   departure.on("change", function () {
        //     let selectedDeparture = $(this).val();
        //     returnDate.attr("min", selectedDeparture);

        //     if (returnDate.val() < selectedDeparture) {
        //       returnDate.val("");
        //     }
        //   });

        // Helper function to initialize from s1/d1, s2/d2 format
        function initializeFromURLSegments() {
            const s1 = getURLParam("s1");
            const d1 = getURLParam("d1");
            const s2 = getURLParam("s2");
            const d2 = getURLParam("d2");

            if (s1 && d1) {
                const [from, to] = s1.split('-');
                setInitialAirportValue('#from', from);
                setInitialAirportValue('#to', to);
                setPickerFromISO(departurePicker, d1);
            }

            if (s2 && d2) {
                setPickerFromISO(returnPicker, d2);
            }
        }

        // Multi-City Functions - Initialize segmentCounter before use
        let segmentCounter = 0;

        // Check route type from URL and initialize accordingly
        const routeType = getURLParam("routeType");

        if (routeType === 'MULTI') {
            $('#multiCitySearch').prop('checked', true);
            $("#standardSearchForm").hide();
            $("#multiCitySearchForm").show();
            initializeMultiCityFromURL();
        } else if (routeType === 'ROUND') {
            $("#returnSearch").prop("checked", true);
            $("#returnDate").prop("disabled", false);
            initializeFromURLSegments();
        } else if (routeType === 'ONEWAY') {
            $("#oneWaySearch").prop("checked", true);
            $("#returnDate").prop("disabled", true).val(null);
            initializeFromURLSegments();
        } else {
            // Fallback: check for s1/d1 format or old URL format
            const s1 = getURLParam("s1");
            const d1 = getURLParam("d1");
            const s2 = getURLParam("s2");
            const d2 = getURLParam("d2");

            if (s1 && d1) {
                if (s2 && d2) {
                    // Round trip or multi
                    const s3 = getURLParam("s3");
                    if (s3) {
                        // Multi city
                        $('#multiCitySearch').prop('checked', true);
                        $("#standardSearchForm").hide();
                        $("#multiCitySearchForm").show();
                        initializeMultiCityFromURL();
                    } else {
                        // Round trip
                        $("#returnSearch").prop("checked", true);
                        $("#returnDate").prop("disabled", false);
                        initializeFromURLSegments();
                    }
                } else {
                    // One way
                    $("#oneWaySearch").prop("checked", true);
                    $("#returnDate").prop("disabled", true).val(null);
                    initializeFromURLSegments();
                }
            } else {
                // Old format fallback
                setInitialAirportValue('#from', getURLParam("arr"));
                setInitialAirportValue('#to', getURLParam("dest"));

                if (!$("#returnDate").val()) {
                    $("#returnDate").prop("disabled", true);
                    $("#oneWaySearch").prop("checked", true);
                } else {
                    $("#returnSearch").prop("checked", true);
                }
            }
        }

        $('#oneWaySearch').change(function() {
            if (this.checked) {
                $("#returnDate").prop("disabled", true).val(null);
                $("#standardSearchForm").show();
                $("#multiCitySearchForm").hide();
            }
        });

        $('#returnSearch').change(function() {
            if (this.checked) {
                $("#returnDate").prop("disabled", false);
                // Try to get return date from URL (d2 parameter)
                const d2 = getURLParam("d2");
                if (d2) {
                    setPickerFromISO(returnPicker, d2);
                }
                $("#standardSearchForm").show();
                $("#multiCitySearchForm").hide();
            }
        });

        $('#multiCitySearch').change(function() {
            if (this.checked) {
                $("#standardSearchForm").hide();
                $("#multiCitySearchForm").show();
                // Only initialize from URL if segments don't exist
                if ($('#multiCitySegments .multi-city-segment').length === 0) {
                    initializeMultiCityFromURL();
                }
            }
        });

        let adults = parseInt(getURLParam("adt")) || 1;
        let children = parseInt(getURLParam("chd")) || 0;
        let infants = parseInt(getURLParam("inf")) || 0;

        $("#flightAdults .count").text(adults);
        $("#flightChildren .count").text(children);
        $("#flightInfants .count").text(infants);

        //   const updatePassengerSummary = () => {
        //     let totalPassengers = adults + children + infants;
        //     $(".passengerDetails").html(`<i class="fa-solid fa-person-walking-luggage"></i> ${totalPassengers} Passenger${totalPassengers > 1 ? "s" : ""}`);
        //   };
        // passenger
        const updatePassengerSummary = () => {
            let totalPassengers = adults + children + infants;
            $(".passengerDetails").html(`
                <i class="fa-solid fa-person-walking-luggage"></i> 
                ${totalPassengers} <span class="passenger-text">Passenger${totalPassengers > 1 ? "s" : ""}</span>
            `);
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

        $(".flightIncrement, .flightDecrement").click(function() {
            let parent = $(this).closest(".quantity");
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

        // Multi-City Functions
        function addMultiCitySegment(segmentData = null) {
            segmentCounter++;
            const segmentIndex = segmentCounter;
            const segmentHtml = `
                <div class="multi-city-segment" data-segment-index="${segmentIndex}">
                    <div class="multi-city-segment-header">
                        <span class="multi-city-segment-title">Flight ${segmentIndex}</span>
                        ${segmentCounter > 1 ? '<button type="button" class="remove-segment-btn" onclick="removeSegment(' + segmentIndex + ')"><i class="fa-solid fa-times"></i> Remove</button>' : ''}
                    </div>
                    <div class="fly">
                        <ul>
                            <li>
                                <a href="#">
                                    <div class="select2-icon-wrapper">
                                        <i class="fa-solid fa-location-dot select2-inner-icon"></i>
                                        <select class="multi-city-from select2" data-segment="${segmentIndex}" data-placeholder="Select Departure City"></select>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="mob-hid">
                                    <i class="fa-solid fa-right-left"></i>
                                </div>
                            </li>
                            <li>
                                <a href="#">
                                    <div class="select2-icon-wrapper">
                                        <i class="fa-solid fa-location-dot select2-inner-icon"></i>
                                        <select class="multi-city-to select2" data-segment="${segmentIndex}" data-placeholder="Flying To (City or Airport)"></select>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="calendar-container flys">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fa-solid fa-calendar-days"></i></span>
                                        <input type="text" class="form-control multi-city-date" data-segment="${segmentIndex}" placeholder="Departure Date">
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            `;
            $('#multiCitySegments').append(segmentHtml);

            // Initialize Select2 for new segment
            const fromSelect = `.multi-city-from[data-segment="${segmentIndex}"]`;
            const toSelect = `.multi-city-to[data-segment="${segmentIndex}"]`;
            setupAirportSelect(fromSelect);
            setupAirportSelect(toSelect);

            // Initialize Flatpickr for new segment
            const dateInput = $(`.multi-city-date[data-segment="${segmentIndex}"]`);
            let minDate = "today";

            // If this is not the first segment, set minDate to the previous segment's date
            if (segmentIndex > 1) {
                const prevDateInput = $(`.multi-city-date[data-segment="${segmentIndex - 1}"]`);
                if (prevDateInput.length && prevDateInput[0]._flatpickr) {
                    const prevDate = prevDateInput[0]._flatpickr.selectedDates[0];
                    if (prevDate) {
                        minDate = prevDate;
                    }
                }
            }

            const datePicker = flatpickr(dateInput[0], {
                dateFormat: "d M Y",
                minDate: minDate,
                onChange: function(selectedDates) {
                    // Update minDate for next segments
                    if (selectedDates.length > 0) {
                        const nextSegmentIndex = segmentIndex + 1;
                        const nextDateInput = $(
                            `.multi-city-date[data-segment="${nextSegmentIndex}"]`);
                        if (nextDateInput.length && nextDateInput[0]._flatpickr) {
                            nextDateInput[0]._flatpickr.set('minDate', selectedDates[0]);
                        }
                    }
                }
            });

            // Set values if provided
            if (segmentData) {
                if (segmentData.from) {
                    setInitialAirportValue(fromSelect, segmentData.from);
                }
                if (segmentData.to) {
                    setInitialAirportValue(toSelect, segmentData.to);
                }
                if (segmentData.date) {
                    setPickerFromISO(datePicker, segmentData.date);
                }
            }
        }

        function removeSegment(index) {
            $(`.multi-city-segment[data-segment-index="${index}"]`).remove();
            renumberSegments();
        }

        function renumberSegments() {
            segmentCounter = 0;
            $('.multi-city-segment').each(function() {
                segmentCounter++;
                const newIndex = segmentCounter;
                $(this).attr('data-segment-index', newIndex);
                $(this).find('.multi-city-segment-title').text(`Flight ${newIndex}`);
                $(this).find('select, input').attr('data-segment', newIndex);
                if (newIndex === 1) {
                    $(this).find('.remove-segment-btn').remove();
                }
            });
        }

        function initializeMultiCityFromURL() {
            const urlParams = new URLSearchParams(window.location.search);

            // Clear existing segments
            $('#multiCitySegments').empty();
            segmentCounter = 0;

            // Parse URL segments
            let segmentIndex = 1;
            let hasSegments = false;

            while (true) {
                const segmentParam = urlParams.get(`s${segmentIndex}`);
                const dateParam = urlParams.get(`d${segmentIndex}`);

                if (!segmentParam || !dateParam) break;

                hasSegments = true;
                const [from, to] = segmentParam.split('-');
                addMultiCitySegment({
                    from: from,
                    to: to,
                    date: dateParam
                });
                segmentIndex++;
            }

            // If no segments found, add one default
            if (!hasSegments) {
                addMultiCitySegment();
            }
        }

        // Make removeSegment available globally
        window.removeSegment = removeSegment;

        // Add segment button handler
        $('#addSegmentBtn').click(function() {
            addMultiCitySegment();
        });

        // Multi-city search handler
        $("#searchMultiCityFlight").click(function(event) {
            event.preventDefault();

            if (!validatePassengerCounts()) return;

            const segments = [];
            $('.multi-city-segment').each(function() {
                const segmentIndex = $(this).data('segment-index');
                const from = $(`.multi-city-from[data-segment="${segmentIndex}"]`).val();
                const to = $(`.multi-city-to[data-segment="${segmentIndex}"]`).val();
                const dateInput = $(`.multi-city-date[data-segment="${segmentIndex}"]`);
                const date = formatDateToISO(dateInput.val());

                if (!from || !to || !date) {
                    alert(`Please fill all fields for Flight ${segmentIndex}.`);
                    return false;
                }

                segments.push({
                    from,
                    to,
                    date,
                    index: segmentIndex
                });
            });

            if (segments.length === 0) {
                alert("Please add at least one flight segment.");
                return;
            }

            let cabinClass = $('input[name="cabinClass"]:checked').val();
            let url = `/flights?routeType=MULTI`;

            segments.forEach(seg => {
                url += `&s${seg.index}=${seg.from}-${seg.to}&d${seg.index}=${seg.date}`;
            });

            url += `&cabinClass=${cabinClass}&adt=${adults}&chd=${children}&inf=${infants}`;

            window.location.href = url;
        });

        // Standard search handler
        $("#searchFlight").click(function(event) {
            event.preventDefault();

            let cabinClass = $('input[name="cabinClass"]:checked').val();
            let from = $('#from').val();
            let destination = $('#to').val();
            let departureDate = formatDateToISO($("#departure").val());
            let returnRaw = $("#returnDate").val();
            let returnDateVal = returnRaw && returnRaw !== "null" ? formatDateToISO(returnRaw) : null;
            let routeType = $('input[name="searchOptions"]:checked').val();

            if (!from || !destination || !departureDate) {
                alert("Please fill all required fields.");
                return;
            }

            if (!validatePassengerCounts()) return;

            // Build URL using s1/d1, s2/d2 format
            let url = `/flights?routeType=${routeType}`;

            // First segment (s1/d1)
            url += `&s1=${from}-${destination}&d1=${departureDate}`;

            // Second segment for round trip (s2/d2)
            if (routeType === 'ROUND' && returnDateVal) {
                // For round trip, s2 is the return route (destination to origin)
                url += `&s2=${destination}-${from}&d2=${returnDateVal}`;
            }

            url += `&cabinClass=${cabinClass}&adt=${adults}&chd=${children}&inf=${infants}`;

            window.location.href = url;
        });

        setupAirportSelect('#from');
        setupAirportSelect('#to');
    });

    // date & years
    function formatDateToISO(dateStr) {
        if (!dateStr) return null;
        // Parse the date string
        const date = new Date(dateStr);

        // Check if date is valid
        if (isNaN(date)) return null;

        // Get components
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0'); // months are 0-based
        const day = date.getDate().toString().padStart(2, '0');

        // Return in YYYY-MM-DD format
        return `${year}-${month}-${day}`;
    }

    function formatISOToReadable(dateStr) {
        if (!dateStr) return null;
        const date = new Date(dateStr);
        if (isNaN(date)) return null;
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        return `${day} ${month} ${year}`;
    }
    /* ==========================
    DROPDOWN SCRIPT
    ========================== */
    document.addEventListener("DOMContentLoaded", function() {
        const wrapper = document.querySelector(".dropdowns");
        if (!wrapper) return;

        const dropdownToggles = wrapper.querySelectorAll(".dropdown-toggle");

        dropdownToggles.forEach(toggle => {
            toggle.addEventListener("click", function(e) {
                e.stopPropagation();
                const menu = this.nextElementSibling;

                // Close all other menus except current
                wrapper.querySelectorAll(".dropdown-menu").forEach(m => {
                    if (m !== menu) m.classList.remove("show");
                });

                // Always toggle current menu
                menu.classList.toggle("show");
            });
        });

        // Click anywhere outside the wrapper → close all dropdowns
        document.addEventListener("click", function() {
            wrapper.querySelectorAll(".dropdown-menu").forEach(menu => menu.classList.remove("show"));
        });

        // Clicking inside menu → stop propagation
        wrapper.querySelectorAll(".dropdown-menu").forEach(menu => {
            menu.addEventListener("click", function(e) {
                e.stopPropagation();
            });
        });
    });

    // Cabin class update
    function updateSelection(ele) {
        document.querySelector(".selected-country").textContent =
            ele.parentNode.textContent.trim();
    }
</script>
