@extends('home/layouts/master')

@section('title', 'Home')
@section('style')
{{-- style --}}
@endsection
@section('content')
{{-- @dd('okok') --}}
    <section class="mainBanner wow fadeInLeft" style="background-image:url(assets/images/banner/banner.png); ">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <x-search-flight />
                </div>
            </div>      
        </div>
    </section>

    <section class="help-line wow fadeInRight">
        <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="hep">
                    <ul>
                    <li><a href="tel:92 01234567 0">
                            <div class="main-flex">
                                <div class="icon-head-help"><i class="fa-solid fa-headphones"></i></div>
                                <div class="call-content"><span>Call 24/7 Customer Support </span><br> <strong>Speak travel expert</strong></div>
                            </div>
                        </a>
                    </li>
                    <li><a href="#"><i class="fa-solid fa-phone"></i> Call: {{ config('variables.contact.phone') }}</a></li>
                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i> Call: {{ config('variables.contact.phone') }}</a></li>
                    </ul>
                </div>
            </div>
        </div>      
        </div>
    </section>

    <section class="testimonials wow fadeInLeft" style="background-image:url(assets/images/banner/testimonial.png); ">
        <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="testi">
                    <h2>Testimonials</h2>
                    <ul class="index-slider">
                    <li>
                        <div class="tes-bo">
                            <div class="tes-img">
                                <img src="assets/images/tes1.png" alt="">
                            </div>
                            <div class="tesfelx">
                                <div class="tes-prof">
                                <h4>Sebastian</h4>
                                <h5>Graphic design</h5>
                                </div>
                                <div class="start">
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            <div class="tes-content">
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text.</p>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="tes-bo">
                            <div class="tes-img">
                                <img src="assets/images/tes2.png" alt="">
                            </div>
                            <div class="tesfelx">
                                <div class="tes-prof">
                                <h4>Evangeline</h4>
                                <h5>Model</h5>
                                </div>
                                <div class="start">
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            <div class="tes-content">
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text.</p>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="tes-bo">
                            <div class="tes-img">
                                <img src="assets/images/tes3.png" alt="">
                            </div>
                            <div class="tesfelx">
                                <div class="tes-prof">
                                <h4>Alexander</h4>
                                <h5>Software engineer</h5>
                                </div>
                                <div class="start">
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            <div class="tes-content">
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text.</p>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="tes-bo">
                            <div class="tes-img">
                                <img src="assets/images/tes2.png" alt="">
                            </div>
                            <div class="tesfelx">
                                <div class="tes-prof">
                                <h4>Evangeline</h4>
                                <h5>Model</h5>
                                </div>
                                <div class="start">
                                <ul>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                    <li>
                                        <i class="fa-solid fa-star"></i>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            <div class="tes-content">
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text.</p>
                            </div>
                        </div>
                    </li>
                    </ul>
                </div>
            </div>
        </div>      
        </div>
    </section>

    <section class="supourt wow fadeInRight">
        <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="supo">
                    <ul>
                    <li>
                        <div class="sup-bo">
                            <img src="assets/images/sup1.png" alt="">
                            <h2>24/7 Customer Support</h2>
                        </div>
                    </li>
                    <li>
                        <div class="sup-bo">
                            <img src="assets/images/sup2.png" alt="">
                            <h2>Refunds within 48 hours</h2>
                        </div>
                    </li>
                    <li>
                        <div class="sup-bo">
                            <img src="assets/images/sup3.png" alt="">
                            <h2>Secure Transaction Guaranteed</h2>
                        </div>
                    </li>
                    </ul>
                </div>
            </div>
        </div>      
        </div>
    </section>

    <section class="flight wow fadeInLeft">
        <div class="container">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <div class="supo">
                    <ul>
                    <li>
                        <div class="sup-bo">
                            <i class="fa-solid fa-plane-arrival"></i>
                            <div id="shiva"><span class="count">700</span>k+</div>
                            <p>Flights booked</p>
                        </div>
                    </li>
                    <li>
                        <div class="sup-bo">
                            <i class="fa-solid fa-bus"></i>
                            <div id="shiva"><span class="count">300</span>k+</div>
                            <p>Buses booked</p>
                        </div>
                    </li>
                    <li>
                        <div class="sup-bo">
                            <i class="fa-solid fa-house"></i>
                            <div id="shiva"><span class="count">50</span>k+</div>
                            <p>Hotels booked</p>
                        </div>
                    </li>
                    <li>
                        <div class="sup-bo">
                            <i class="fa-solid fa-gauge"></i>
                            <div id="shiva"><span class="count">20</span>m+</div>
                            <p>Kilometres traveled</p>
                        </div>
                    </li>
                    </ul>
                </div>
            </div>
        </div>      
        </div>
    </section>

    <section class="featured wow fadeInRight">
        <div class="container">
        <div class="row align-items-center">
            <div class="col-md-12 col-lg-3">
                <div class="fea">
                    <h2>Featured Partners</h2>
                    <p>Domestic & International</p>
                </div>
            </div>
            <div class="col-md-12 col-lg-9">
                <div class="feaul">
                    <ul class="m-silder">
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client2.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client3.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client4.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client5.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client6.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client7.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client8.png" alt="">
                        </div>
                    </li>
                    <li>
                        <div class="fea-img">
                            <img src="assets/images/client5.png" alt="">
                        </div>
                    </li>
                    </ul>
                </div>
            </div>
        </div>      
        </div>
    </section>
@endsection
@section('script')
<script>
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('dropdownMenu2');
        const toggle = document.getElementById('dropdownToggle2');

        if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    function updateSelection(radio) {
        const dropdownToggle = document.getElementById('dropdownToggle2');
        const selectedText = dropdownToggle.querySelector(".selected-country"); // Ensure correct selection
        selectedText.textContent = radio.parentElement.textContent.trim();
        document.getElementById('dropdownMenu2').style.display = 'none'; // Close dropdown
    }
</script>
<script>
    
</script>
@endsection