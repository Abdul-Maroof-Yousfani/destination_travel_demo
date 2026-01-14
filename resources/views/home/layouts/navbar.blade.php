<header>
    <div class="top-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <div class="menuWrap2 menuWrap2-top">
                        <ul class="menu2 menu2-top">
                            
                            <li><a href="tel:92 01234567 0">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-solid fa-phone"></i></div>
                                        <div class="call-content"><span>Call 24/7 </span> <strong>{{ config('variables.contact.phone') }}</strong></div>
                                    </div>
                                </a>
                            </li>
                            <li><a href="tel:92 01234567 0">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-brands fa-whatsapp"></i></div>
                                        <div class="call-content"><span>Whatsapp 24/7</span> <strong>92 01234567 0</strong></div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown" id="dropdown4">
                                    <div class="dropdown-toggle" id="dropdownToggle4">
                                        <span class="selected-country" id="selectedCountry4">
                                            <span class="flag-icon flag-icon-us"></span> USD
                                        </span>
                                    </div>
                                    <div class="dropdown-menu" id="dropdownMenu4"></div>
                                </div>
                            </li>
                        
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-1 text-left">
                    <div class="menu-Bar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
                <div class="col-md-3 text-left">
                    <div class="logo">
                        <a href="{{route('home')}}" class="logo">
                            <img src="{{ asset('assets/images/logoCG.png') }}" alt="travelandtourLogo">
                        </a>
                    </div>
                </div>
                <div class="col-md-8 text-left">
                    <div class="menuWrap">
                        <ul class="menu">
                            <li class="active"><a href="{{route('home')}}">Home</a></li>
                            <li><a href="#">Company</a></li>
                            <li><a href="#">Products</a></li>
                            <li><a href="#">Contact Us</a></li>
                            <li><a href="{{ route('search.booking') }}"><i class="fa-solid fa-magnifying-glass"></i> Search Booking</a></li>
                            <li><a href="tel:{{ config('variables.contact.phone') }}">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-solid fa-phone"></i></div>
                                        <div class="call-content"><span>Call 24/7 </span><br> <strong>{{ config('variables.contact.phone') }}</strong></div>
                                    </div>
                                </a>
                            </li>
                            <li><a href="tel:+923123456789">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-brands fa-whatsapp"></i></div>
                                        <div class="call-content"><span>Whatsapp 24/7</span> <br> <strong>+923123456789</strong></div>
                                    </div>
                                </a>
                            </li>
                            @auth('client')
                                <li>
                                    <a href="{{ route('login') }}"><i class="fa-regular fa-user"></i> Profile</a> &nbsp;
                                    <a type="button" class="logoutBtn"><i class="fa fa-sign-out"></i> Logout</a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}"><i class="fa-regular fa-user"></i> Sign In</a>
                                </li>
                            @endauth
                          
                        </ul>
                    </div>
                    <div class="menuWrap2">
                        <ul class="menu2">
                            <li><a href="{{ route('search.booking') }}"><i class="fa-solid fa-magnifying-glass"></i> Search Booking</a></li>
                            <li><a href="tel:{{ config('variables.contact.phone') }}">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-solid fa-phone"></i></div>
                                        <div class="call-content"><span>Call 24/7 </span><br> <strong>{{ config('variables.contact.phone') }}</strong></div>
                                    </div>
                                </a>
                            </li>
                            <li><a href="tel:+923123456789">
                                    <div class="main-flex">
                                        <div class="icon-head"><i class="fa-brands fa-whatsapp"></i></div>
                                        <div class="call-content"><span>Whatsapp 24/7</span> <br> <strong>+923123456789</strong></div>
                                    </div>
                                </a>
                            </li>
                            {{-- <li>
                                <div class="dropdown" id="dropdown6">
                                    <div class="dropdown-toggle" id="dropdownToggle6">
                                        <span class="selected-country" id="selectedCountry6">
                                            <span class="flag-icon flag-icon-pk"></span> PKR
                                        </span>
                                    </div>
                                    <div class="dropdown-menu" id="dropdownMenu6"></div>
                                </div>
                            </li> --}}
                            @auth('client')
                                <li>
                                    <a href="{{ route('profile') }}"><i class="fa-regular fa-user"></i> Profile</a> &nbsp;
                                    <a type="button" class="logoutBtn"><i class="fa fa-sign-out"></i> Logout</a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route('login') }}"><i class="fa-regular fa-user"></i> Sign In</a>
                                </li>
                            @endauth
                        </ul>
                    </div>
                    <div class="menuWrap2 menuWrap3">
                        <ul class="menu2">
                            <li><a href="{{route('home')}}"><i class="fa-solid fa-magnifying-glass"></i></a></li>

                            <li></li>

                            <li><a href="#"><i class="fa-regular fa-user"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
{{-- @dd(auth()->guard('client')->user()) --}}