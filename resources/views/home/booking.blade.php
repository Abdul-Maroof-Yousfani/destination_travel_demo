@extends('home/layouts/master')

@section('title', 'Booking')
@section('style')
<style>
   .plane{border-radius:15px;overflow:scroll;scrollbar-width:thin;padding:105px 105px 105px 250px;}
  .seat-row{display:flex;flex-direction:column;align-items:center;gap:5px;margin:5px}
  .seat{cursor:pointer;border:solid 1px #b9b9b9;width:39px;height:36px;border-radius:4px;padding:8px 0px;margin-top:6px;position:relative;}
  .seat:before{content:"";display:block;width:30px;height:26px;border:solid 1px #88857c;border-left:none;border-radius:0 5px 5px 0;position:absolute;margin:-4px -1px;}
  .seat.selected{background-color:green;color:white;}
  .seat.occupied{background-color:#d9d8d6;color:#000000;pointer-events:none;}
  .aisle{height:20px;}
  .mealCard:hover,.baggageCard:hover{background-color:#127F9F;color:#ffff;box-shadow:0 5px 15px rgba(0,0,0,0.2);}
  /* .footerTimeOutContainer{z-index:9999;position:fixed;bottom:0;left:0;right:0;background-color:#127f9fe0;color:#fff;padding:15px;transition:opacity 0.5s ease-in-out;} */
  .addOnsContainer{width:100%;}
  .tabPlane{display:flex;flex-direction:column;justify-content:space-between;position:relative;min-width:min-content;margin:auto;border:2px solid #c5c5c7;min-height:280px;border-right:none;border-left:none;padding:10px;background:#fff;left:250px;top:0px;margin-bottom:115px;margin-top:115px;}
  .tabPlane:before{content:"";position:absolute;height:336px;width:548px;padding-left:115px;border-radius:60% 0% 0% 60%;border:2px solid #c5c5c7;left:-263px;top:-2px;border-right:none;}
  .tabPlane:after{content:"";position:absolute;height:336px;width:548px;padding-left:115px;border-radius:0% 60% 60% 0%;border:2px solid #c5c5c7;right:-263px;top:-2px;border-left:none;}
  .winds1{position:relative;}
  .winds1:before{content:"";position:absolute;top:-104px;right:90px;width:250px;height:94px;border-bottom-left-radius:30px;border-right:none !important;border-top:none !important;border:2px solid #c5c5c7;}
  .winds1:after{content:"";position:absolute;width:250px;height:94px;border:2px solid #c5c5c7;border-bottom-right-radius:60px;border-top:none;border-left:none;transform:skew(-35deg);left:322px;top:-104px;}
  .winds2{position:relative;}
  .winds2:after{content:"";position:absolute;width:250px;height:94px;border:2px solid #c5c5c7;border-top-right-radius:60px;border-bottom:none;border-left:none;transform:skew(35deg);left:303px;bottom:-104px;}
  .winds2:before{content:"";position:absolute;bottom:-104px;left:705px;width:250px;height:94px;border:2px solid #c5c5c7;border-top-left-radius:30px;border-right:none;border-bottom:none;}
 .windows{position:relative;}
.windows:before{content:"";position:absolute;top:70px;left:-242px;width:100px;height:150px;background-image:url(/assets/images/windows.png);}
.windows:after{content:"";position:absolute;top:70px;left:-66px;width:37px;height:150px;background-image:url(/assets/images/lavatory.png);}
.windows2{position:relative;}
.ancillary-card{transition:0.2s;}
.ancillary-card:hover{transform:scale(1.03);box-shadow:0 0 10px rgba(0,0,0,0.15);}
.ancillary-card.selected{border:2px solid #28a745;}
.ancillary-card.disabled{opacity:0.6;cursor:not-allowed;}

</style>
@endsection
@section('content')
@if (!isset($data) || empty($data))
   <script>
      Swal.fire({
         title: 'Please search for the flight again, the data has been lost.',
         icon: 'info',
         confirmButtonText: 'Go Back',
         allowOutsideClick: false,
         allowEscapeKey: false,
         preConfirm: () => {
            _loader('show');
            let goBack = localStorage.getItem('flights') || null;
            goBack ? window.location.href = `/flights${goBack}` : window.history.back();
         }
      });
   </script>
@endif
@php
   // dd($data);
   $isEmirate = isset($data['airline']) && $data['airline'] === 'emirate';
   $isFlyJinnah = isset($data['airline']) && $data['airline'] === 'flyjinnah';
   $isPia = isset($data['airline']) && $data['airline'] === 'pia';
   $isAirblue = isset($data['airline']) && $data['airline'] === 'airblue';
@endphp
<section class="bookings wow fadeInLeft">
   <div class="container">
      <div class="row">
         <div class="col-md-12 col-lg-12">
            <div class="books">
               <form id="msform">
                  <!-- progressbar -->
                  <div class="steps-fom">
                     <ul id="progressbar">
                        <li class="active" id="account"><strong><p>Booking</p></strong></li>
                        <li id="personal"><strong><p>Payment</p></strong></li>
                        <li id="payment"><strong><p>Ticket</p></strong></li>
                        <!-- <li id="confirm"><strong>Finish</strong></li> -->
                     </ul>
                  </div>
                  {{-- @dd($data, $totalFare, $tax) --}}
                  <!-- booking form -->
                  <fieldset>
                     <div class="form-card">
                        <div class="row row2">
                           <div class="col-md-12 col-lg-8">
                                 <x-Passengers :flightData="$data" />
                                 {{-- <x-passengers-test :flightData="$data" /> --}}
                           </div>
                           <div class="col-md-12 col-lg-4">
                              {{-- @dd($totalFare) --}}
                              <x-flight-and-price :flightData="$data" :totalFare="$totalFare" :tax="$tax" />
                           </div>
                           <div class="addOnsContainer">
                              @if (isset($data['isDirectBooking']) && !$data['isDirectBooking'])
                              <h2 class="my-3 text-info font-weight-bolder">Add-Ons</h2>
                              <div class="card">
                                 <div class="card-header d-flex justify-content-between">
                                    <div>
                                       <div data-targetit="box-021" class="btn btn-outline-info mx-2 current" data-toggle="tab">Seat</div>
                                       <div data-targetit="box-022" class="btn btn-outline-info mx-2" data-toggle="tab">Meal</div>
                                       <div data-targetit="box-023" class="btn btn-outline-info mx-2" data-toggle="tab">Baggage</div>
                                    </div>
                                    {{-- <div class="btn btn-light" id="skipAncis">Skip to payment</div> --}}
                                 </div>   
                                 <div class="card-body">
                                    <div class="box-021 tab-content showfirst">
                                       <div class="d-flex border border-dark p-2 seatFlightSegments"></div>
                                       <div class="container mt-4">
                                          <div class="plane"></div>
                                       </div>
                                    </div>
                                    <div class="box-022 tab-content">
                                       <div class="d-flex border border-dark p-2 mealFlightSegments"></div>
                                       <div class="meals container mt-4"></div>
                                    </div>
                                    <div class="box-023 tab-content">
                                       <div class="d-flex border border-dark p-2 baggageFlightSegments"></div>
                                       <div class="baggages container mt-4"></div>
                                    </div>
                                 </div>
                              </div>
                              @endif
                           </div>
                        </div>
                     </div>
                     <input type="button" name="next" class="next btn btn btn-b" id="contactSubmit" value="Continue" />
                  </fieldset>
                  <!-- payment -->
                  <fieldset>
                     <div class="form-card">
                        <div class="row">
                           <div class="col-md-12 col-lg-8">
                              <div class="pays">
                                 <h2>select a payment method</h2>
                                 <ul>
                                    <li><div class="payicon"><i class="fa-regular fa-credit-card"></i> <p>Secure Payments</p> </div></li>
                                    <li><div class="payicon"><i class="fa-solid fa-bolt"></i> <p>Quick Refunds</p> </div></li>
                                    <li><div class="payicon"><i class="fa-regular fa-thumbs-up"></i> <p>Trusted by 10 lac customers</p> </div></li>
                                 </ul>
                              </div>
                              <div class="pay-taps">
                                 <div class="row">
                                    <div class="col-md-12 col-lg-4 br-right">
                                       <div class="payments-taps-lists">
                                          <ul class="tab-product  wow fadeInRight">
                                             <li data-targetit="box-10">
                                                <a href="#tab-10" data-toggle="tab">
                                                   <div class="payflexpay">
                                                      <div class="payment-option">
                                                         <label for="jazzcash">JazzCash</label>
                                                      </div>
                                                      <div class="pay-logo">
                                                         <img src="/assets/images/jazz.png" alt="">
                                                      </div>
                                                   </div>
                                                </a>
                                             </li>
                                             <li data-targetit="box-9" class="current">
                                                <a href="#tab-9" data-toggle="tab">
                                                   <div class="payflexpay">
                                                      <div class="payment-option">
                                                         <label for="hbl">HBL Direct Transfer</label>
                                                            <div class="savpkr">
                                                               <p>Save PKR 4,223</p>
                                                            </div>
                                                      </div>
                                                      <div class="pay-logo">
                                                            <img src="/assets/images/hbl.png" alt="">
                                                      </div>
                                                   </div>
                                                </a>
                                             </li>
                                          </ul>
                                       </div>
                                    </div>
                                    <div class="col-md-12 col-lg-8 ">
                                       {{-- HBL --}}
                                       <div class="box-9 showfirst tab-content">
                                          <div class="payment-radio">
                                             <div class="coinfex">
                                                <div class="cois">
                                                   <i class="fa-solid fa-coins"></i>
                                                </div>
                                                <div class="coin-sas">
                                                   <h2>Sasta Wallet</h2>
                                                   <p>Login to access wallet.</p>
                                                </div>
                                             </div>
                                             <div class="cois-butt">
                                                <a href="#" class="btn btn-c">Login</a>
                                             </div>
                                          </div>
                                          <div class="custom-method">
                                             <div class="bulco">
                                                <i class="fa-solid fa-circle"></i>
                                                <p> Click continue to receive your customer ID</p>
                                             </div>
                                          </div>
                                          <div class="method-feild">
                                             <!-- <div class="form-group">
                                                <label for="">select Transfer Method</label> 
                                                <select class="form-control" name="" id="">
                                                   <option></option>
                                                   <option></option>
                                                   <option></option>
                                                </select>
                                             </div> -->
                                             <div class="form-group">
                                             <label for="paymentType">Select Bank</label>
                                                <select class="form-control" name="paymentType" id="paymentType">
                                                   <option value="card">Card payment</option>
                                                   <option value="cash">Pay cash at your nearest travelandtour franchise.</option>
                                                </select>
                                             </div>
                                          </div>
                                          <div class="voucher">
                                             <h2>Please <span>login</span> to avail discounts on voucher codes.</h2>
                                             <p>By selecting to complete this booking, I acknowledge that I have read and accept the above Policy section containing Fare Rules & Restrictions, <span><a href="#">Terms of Use</a></span> and <span><a href="#">Privacy Policy</a></span>.  </p>
                                          </div>
                                       </div>
                                       <div class="box-10 tab-content">
                                          <div class="payment-radio">
                                             <div class="coinfex">
                                                <div class="cois">
                                                   <i class="fa-solid fa-coins"></i>
                                                </div>
                                                <div class="coin-sas">
                                                   <h2>Sasta Wallet</h2>
                                                   <p>Login to access wallet.</p>
                                                </div>
                                             </div>
                                             <div class="cois-butt">
                                                <a href="#" class="btn btn-c">Login</a>
                                             </div>
                                          </div>

                                          <div class="custom-method">
                                             <div class="bulco">
                                                <i class="fa-solid fa-circle"></i>
                                                <p> Click continue to receive your customer ID</p>
                                             </div>
                                             <div class="bulco">
                                                <i class="fa-solid fa-circle"></i>
                                                <p> Click continue to receive your customer ID</p>
                                             </div>
                                             <div class="bulco">
                                                <i class="fa-solid fa-circle"></i>
                                                <p> Click continue to receive your customer ID</p>
                                             </div>
                                          </div>

                                          <div class="method-feild">
                                             <div class="form-group">
                                                <label for="">select Transfer Method</label> 
                                             <select class="form-control" name="" id="">
                                                <option></option>
                                                <option></option>
                                                <option></option>
                                             </select>
                                             </div>
                                             <div class="form-group">
                                                <label for="">select Bank</label> 
                                             <select class="form-control" name="" id="">
                                                <option></option>
                                                <option></option>
                                                <option></option>
                                             </select>
                                             </div>
                                          </div>


                                          <div class="voucher">
                                             <h2>Please <span>login</span> to avail discounts on voucher codes.</h2>
                                             <p>By selecting to complete this booking, I acknowledge that I have read and accept the above Policy section containing Fare Rules & Restrictions, <span><a href="#">Terms of Use</a></span> and <span><a href="#">Privacy Policy</a></span>.  </p>
                                          </div>

                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="col-md-12 col-lg-4">
                              <x-flight-and-price :flightData="$data" :totalFare="$totalFare" :tax="$tax" priceclass="paymentPriceContainer"/>
                           </div>
                        </div>
                     </div>
                     {{-- <input type="button" name="previous" class="previous btn btn btn-b" value="Previous" /> --}}
                     <input type="button" name="next" class="next d-none" id="paymentSend"/>
                     <input type="button" class="btn btn-b" value="Continue" id="paymentSendTest"/>
                     {{-- <div class="custom-control custom-switch d-flex m-3">
                        <input type="checkbox" class="custom-control-input" checked id="paymentOnHold">
                        <label class="custom-control-label paymentOnHoldText" for="paymentOnHold">Payment is ON HOLD</label>
                      </div> --}}
                  </fieldset>
                  <!-- payment done -->
                  <fieldset>
                     <div class="form-card">
                        <div class="row">
                           <div class="col-md-12 col-lg-8">
                              <div class="tyous">
                                 <h2>Thank You, <span class="guestName"></span>!</h2>
                                 <p>You’re one step away from traveling to 
                                    <span class="font-weight-bold">
                                       @if (isset($data) && !empty($data))
                                          @if ($isEmirate)
                                             {{$data['flightDetails']['segments'][0]['flights']['Arrival']['AirportCode']['value'] ?? ''}}
                                          @elseif ($isFlyJinnah)
                                             {{$data['departureFlight']['destinationCode'] ?? ''}}
                                          @elseif ($isAirblue)
                                             {{$data['departure']['arrival']['airport'] ?? ''}}
                                          @else
                                             {{$data['departure']['arrival']['airport'] ?? ''}}
                                          @endif
                                       @endif
                                    </span>
                                 !</p>
                                 <p class="ticketMsg text-decoration-underline"></p>
                                 <h3><span>Order ID: <span class="orderId copyBtn font-weight-bolder"></span></span></h3>
                              </div>
                              <div class="steps">
                                 <h4>Next Steps</h4>
                                 <div class="custom-method setp-bult">
                                    <div class="bulco">
                                       <i class="fa-solid fa-circle"></i>
                                       <p> Consumer ID: Please make a payment against Consumer ID: <span class="orderId font-weight-bolder"></p>
                                    </div>
                                    <div class="bulco">
                                       <i class="fa-solid fa-circle"></i>
                                       <p> Confirmation: You will receive an email with your e-ticket confirmation and a confirmation WhatsApp message once the payment is verified.</p>
                                    </div>
                                    <div class="bulco">
                                       <i class="fa-solid fa-circle"></i>
                                       <p> Excise Duty: Please add PKR 2,000 for excise duty on ticket prices above PKR 300,000.</p>
                                    </div>
                                    <div class="bulco">
                                       <i class="fa-solid fa-circle"></i>
                                       <p> Payment Help: Contact <a href="tel:+{{ config('variables.contact.phone') }}">{{ config('variables.contact.phone') }}</a> if you need assistance. </p>
                                    </div>
                                 </div>
                              </div>
                              <x-flight-and-price-ticket :flightData="$data" :totalFare="$totalFare" :tax="$tax" />
                              <div class="steps">
                                 <h4>Traveler(s)</h4>
                                 <div class="contactDetails"></div>
                              </div>
                           </div>
                           <div class="col-md-12 col-lg-4">
                              <div class="bokkings-bar bokkings-bar5 mb-3">
                                 <div class="book-head  book-head2 ">
                                    <div class="youbook">
                                       <h2>Price Summary</h2>
                                    </div>
                                    <div class="depar-head">
                                       <ul>
                                          <li>
                                             <p>Departing</p>
                                          </li>
                                          <li>
                                             @if (isset($data) && !empty($data))
                                                @if ($isEmirate)
                                                   <p><i class="fa-regular fa-calendar"></i> {{\Carbon\Carbon::parse($data['flightDetails']['segments'][0]['flights']['Departure']['Date']['value'])->format('d M Y') ?? ''}}</p>
                                                @elseif ($isFlyJinnah)
                                                   <p><i class="fa-regular fa-calendar"></i> {{$data['departureFlight']['departureDate']}}</p>
                                                @endif
                                             @endif
                                          </li>
                                       </ul>
                                    </div>
                                 </div>
                                 <div class="paxWithPrice"></div>
                                 <div class="pri-eid">
                                    <p><span>Tax</span></p>
                                    <p><span class="taxPaid"></span></p>
                                 </div> 
                                 <div class="pri-eid">
                                    <p><span>Price You Pay</span></p>
                                    <p><span class="totalPricePaid"></span></p>
                                 </div> 
                              </div>
                              <div class="bokkings-bar bokkings-bar5 emiTimeLimitContainer d-none mb-3">
                                 <div class="book-head  book-head2 ">
                                    <div class="youbook">
                                       <h2>Time Limits</h2>
                                    </div>
                                 </div>
                                 <div class="timeLimitsEmi"></div>
                              </div>
                              <!-- Tax Section -->
                              <div class="bokkings-bar bokkings-bar5 emiTaxContainer d-none mb-3">
                                 <div class="book-head book-head2">
                                    <div class="youbook">
                                       <h2>Tax details</h2>
                                    </div>
                                    <div class="youbook">
                                       <p class="text-info font-weight-bolder toggle-tax-details pointer">Show more</p>
                                    </div>
                                 </div>
                                 <div class="taxDetailsEmi" style="display: none;"></div>
                              </div>
                              <!-- Service Section -->
                              <div class="bokkings-bar bokkings-bar5 emiServiceContainer d-none mb-3">
                                 <div class="book-head book-head2">
                                    <div class="youbook">
                                       <h2>Service details</h2>
                                    </div>
                                    <div class="youbook">
                                       <p class="text-info font-weight-bolder toggle-service-details pointer">Show more</p>
                                    </div>
                                 </div>
                              <div class="serviceDetailsEmi" style="display: none;"></div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="fligth-btn">
                        <a class="btn btn-c" href="{{ route('home') }}" role="button">Back to Flight</a> 
                     </div>
                  </fieldset>
               </form>
            </div>
         </div>
      </div>      
   </div>
</section>
{{-- <x-session-timeout-container/> --}}
@endsection
@section('script')
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
   let passengers = [];
   let lastSubmittedData = null;
   let isSubmitting = false;
   let paymentdata;
   let orderId;
   let current = 1, steps = $("fieldset").length;
   let tax = @json($tax);
   let flightType = @json($data['isLocal']);
   function setProgressBar(step) {
      $(".progress-bar").css("width", (100 / steps * step) + "%");
   }
   function validateFields(fieldset) {
      let isValid = true;
      fieldset.find("input[required], select[required]").each(function () {
         if (!$(this).val()) {
            isValid = false;
            $(this).addClass("border-danger");
         } else {
            $(this).removeClass("border-danger");
         }
      });
      return isValid;
   }
   $(document).on('input change', '.paxDetails input, .paxDetails select, .contact2 input, .contact2 select, #userFullName, #userEmail, #userPhone,   #userPhoneCode', function () {
      lastSubmittedData = null;
      isSubmitting = false;
   });
   $('#contactSubmit').click(function () {
      if (isSubmitting) return;
      isSubmitting = true;
      passengers = [];
      let hasError = false;
      let firstErrorField = null;

      $('.paxDetails .contact2').each(function () {
         let passenger = {
            id: ($(this).find('input[name$="_id[]"]').val() || "").trim(),
            type: ($(this).find('input[name$="_type[]"]').val() || "").trim(),
            name: ($(this).find('input[name$="_name[]"]').val() || "").trim(),
            surname: ($(this).find('input[name$="_surname[]"]').val() || "").trim(),
            title: ($(this).find('input[name^="title_"]:checked').val() || "").trim(),
            dob: ($(this).find('input[name$="_dob[]"]').val() || "").trim(),
            nationality: ($(this).find('select[name$="_nationality[]"]').val() || "").trim(),
            passportNumber: ($(this).find('input[name$="_passportnumber[]"]').val() || "").trim(),
            passportExpiry: ($(this).find('input[name$="_passportexp[]"]').val() || "").trim()
         };

         $(this).find("input[required], select[required]").each(function () {
            if (!$(this).val()) {
               $(this).addClass("border-danger");
               hasError = true;
               if (!firstErrorField) firstErrorField = this;
            } else {
               $(this).removeClass("border-danger");
            }
         });

         passengers.push(passenger);
      });
      let selectedCity = $('#userCity option:selected');
      let userData = {
         title: $('input[name="user_title"]:checked').val(),
         fullName: ($('#userFullName').val() || "").trim(),
         email: ($('#userEmail').val() || "").trim(),
         phoneCode: ($('#userPhoneCode').val() || "").trim(),
         phone: ($('#userPhone').val() || "").trim(),
         city: (selectedCity.text() || "").trim()
      };

      if (!userData.fullName || !userData.email || !userData.phoneCode || !userData.phone || !userData.city) {
         hasError = true;
         if (!firstErrorField) {
            if (!userData.fullName) firstErrorField = $('#userFullName');
            else if (!userData.email) firstErrorField = $('#userEmail');
            else if (!userData.phoneCode) firstErrorField = $('#userPhoneCode');
            else if (!userData.phone) firstErrorField = $('#userPhone');
            else if (!userData.city) firstErrorField = $('#userCity');
         }
      }
      if (hasError) {
         if (firstErrorField) $(firstErrorField).focus();
         _alert('Please fill all required fields', 'warning');
         isSubmitting = false;
         return false;
      }
      let normalizedData = normalizeData({ passengers, userData });
      let currentData = JSON.stringify(normalizedData);

      if (lastSubmittedData && currentData === lastSubmittedData) {
         // _alert('No changes detected. Data already submitted.', 'info');
         isSubmitting = false;
         return;
      }

      lastSubmittedData = currentData;

   });

   function normalizeData(psngr) {
      if (Array.isArray(psngr)) {
         return psngr.map(item => normalizeData(item));
      } else if (typeof psngr === 'object' && psngr !== null) {
         const sortedKeys = Object.keys(psngr).sort();
         const normalized = {};
         sortedKeys.forEach(key => {
            normalized[key] = normalizeData(psngr[key]);
         });
         return normalized;
      } else if (typeof psngr === 'string') {
         return psngr.trim();
      } else {
         return psngr ?? '';
      }
   }
   // function getSegmentAttributes(flightNo) {
   //    let segmenArry = data['segments'][0] ? data['segments'] : [data['segments']];
   //    return segmenArry.find(s => s.flightNumber === flightNo);
   // }
   $('#paymentSendTest').click(function () {
      let paymentType = $('#paymentType').val();
      // console.log('paymentType');
      // return;

      if(paymentType === 'card') {
         paymentdata = 'test';
         paymentAjax();
      }
      else if(paymentType === 'cash') {
         localStorage.setItem('approvePayCash', JSON.stringify(true));
         (async () => {
            let alMsg = 'Flight booked successfully. Please complete payment before the deadline, otherwise it will be canceled.';
            await _confirm(alMsg, false, 'info', 'Continue')
         })();
         showOnHoldBooking();
      }
   });
   const paymentAjax = () => {
      let isProcessing = false;
      $.ajax({
         type: "POST",
         url: "{{route('payment')}}",
         data: {
            paymentdata: paymentdata || {},
            _token: "{{ csrf_token() }}"
         },
         beforeSend: () => _loader('show'),
         success: function (response) {
            approveBookingAjax();
         },
         error: function (xhr) {
            (async () => {
               let phone = "{{ config('variables.contact.phone') }}";
               let alMsg = xhr.responseJSON.message || `Payment processing error. If the amount has been deducted from your account, please contact us at +${phone} your Order Id is ${orderId}.`;
               if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                  let goBack = localStorage.getItem('flights') || null;
                  goBack ? window.location.href = `/flights${goBack}` : window.history.back();
               }
            })();
            // _alert(xhr.responseJSON.message, "error");
         },
         complete: () => _loader('hide')
      });
   };
   const verifyClient = () => {
      return new Promise((resolve, reject) => {
         $.ajax({
            type: "POST",
            url: "{{ route('verify.client') }}",
            data: {
               email: $('#userEmail').val(),
               phone: $('#userPhone').val(),
               _token: "{{ csrf_token() }}"
            },
            beforeSend: () => _loader('show'),
            success: function (res) {
               console.log(res);
               resolve(true);
            },
            error: function (xhr) {
               let title = xhr.responseJSON?.message || 'Verify Client Error';
               Swal.fire({
                  title,
                  icon: 'warning',
                  confirmButtonText: "Login",
                  cancelButtonText: "Change Email",
                  showCancelButton: true,
                  showCloseButton: true
               }).then((result) => {
                  if (result.isConfirmed) {
                     window.location.href = "{{ route('login') }}";
                  }
               });
               reject(false);
            },
            complete: function () {
               _loader('hide');
            }
         });
      });
   };
   function showTicketPage() {
      const fieldsets = $("fieldset");
      const lastIndex = fieldsets.length - 1;
      const last_fs = fieldsets.eq(lastIndex);
      fieldsets.hide();
      $("#progressbar li").removeClass("active");
      $("#progressbar li").slice(0, lastIndex + 1).addClass("active");
      last_fs.show();
      current = lastIndex;
      setProgressBar(current);
   }
   function showPaymentPage() {
      const fieldsets = $("fieldset");
      const paymentIndex = 1; // 0 = booking form, 1 = payment, 2 = thank you
      fieldsets.hide();
      $("#progressbar li").removeClass("active");
      $("#progressbar li").slice(0, paymentIndex + 1).addClass("active");
      fieldsets.eq(paymentIndex).show();
      current = paymentIndex;
      setProgressBar(current);
   }
   function showMissingDataMsg() {
      (async () => {
         let alMsg = 'Data is missing. Please search for the flight again.';
         if (await _confirm(alMsg, false, 'warning', 'GoBack')) {
            let goBack = localStorage.getItem('flights') || null;
            goBack ? window.location.href = `/flights${goBack}` : window.history.back();
         }
      })();
   }
   $(".toggle-tax-details").on("click", function () {
      const $toggleBtn = $(this);
      const $details = $toggleBtn.closest(".emiTaxContainer").find(".taxDetailsEmi");

      $details.slideToggle(200, function () {
         const isVisible = $details.is(":visible");
         $toggleBtn.text(isVisible ? "Show less" : "Show more");
      });
   });
   const paxCapitalize = (type) => {
      return type?.toUpperCase() === 'ADT' ? 'Adult'
         : type?.toUpperCase() === 'CNN' ? 'Child'
         : type?.toUpperCase() === 'INF' ? 'Infant'
         : type || '';
   };
   function showOnHoldBooking(){
      let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
      let approvePayCash = JSON.parse((localStorage.getItem('approvePayCash') || false));
      let ticketIssued = JSON.parse((localStorage.getItem('ticketIssued') || false));
      
      if (createdBooking) {
         orderId = createdBooking.booking.order_id;
         showPaymentPage();
      }
      if(createdBooking && (approvePayCash || ticketIssued)) {
         // sessionTimer(false);
         setTicketPage(createdBooking);
         showTicketPage();
      }
   }
   $(document).ready(function () {
      showOnHoldBooking();
   });
   const getUserDetails = () => {
      let selectedCity = $('#userCity option:selected');
      let cityText = (selectedCity.text() || "").trim();
      let cityValue = (selectedCity.val() || "").trim();
      return {
            domestic: flightType ? 1 : 0,
            title: $('input[name="user_title"]:checked').val(),
            userFullName: $('#userFullName').val(),
            userEmail: $('#userEmail').val(),
            userPhoneCode: $('#userPhoneCode').val(),
            userPhone: $('#userPhone').val(),
            acceptOffers: $('#acceptOffers').is(':checked'),
            cityCode: cityValue,
            city: cityText,
            country: selectedCity.data('country') || '',
            countryCode: selectedCity.data('country-code') || '',
         }
   }
   $(document).on("click", ".toggle-panelties-details", function () {
      const $btn = $(this);
      const $details = $btn.closest(".penaltiesContainer").find(".panelties-details");
      const $icon = $btn.find(".toggle-icon");
      const $text = $btn.find(".toggle-text");

      $details.slideToggle(300, function () {
         const isVisible = $details.is(":visible");
         $text.text(isVisible ? "Hide details" : "Show details");
         $icon.toggleClass("fa-chevron-down fa-chevron-up");
         $icon.toggleClass("rotate-180");
      });
   });
   $(".toggle-service-details").on("click", function () {
      const $toggleBtn = $(this);
      const $details = $toggleBtn.closest(".emiServiceContainer").find(".serviceDetailsEmi");

      $details.slideToggle(200, function () {
         const isVisible = $details.is(":visible");
         $toggleBtn.text(isVisible ? "Show less" : "Show more");
      });
   });
   function setAndLockForm(passengers, userData) {
      $('.paxDetails .contact2').each(function (index) {
         const pax = passengers[index];
         if (!pax) return;

         $(this).find('input[name$="_id[]"]').val(pax.id || '');
         $(this).find('input[name$="_type[]"]').val(pax.type || '');
         $(this).find('input[name$="_name[]"]').val(pax.name || '');
         $(this).find('input[name$="_surname[]"]').val(pax.surname || '');
         $(this).find(`input[name^="title_"][value="${pax.title}"]`).prop('checked', true);
         $(this).find('input[name$="_dob[]"]').val(pax.dob || '');
         $(this).find('select[name$="_nationality[]"]').val(pax.nationality || '');
         $(this).find('input[name$="_passportnumber[]"]').val(pax.passportNumber || '');
         $(this).find('input[name$="_passportexp[]"]').val(pax.passportExpiry || '');
      });

      // ===== USER DATA =====
      if (userData?.title) {
         $(`input[name="user_title"][value="${userData.title}"]`).prop('checked', true);
      }

      $('#userFullName').val(userData.userFullName || '');
      $('#userEmail').val(userData.userEmail || '');
      $('#userPhoneCode').val(userData.userPhoneCode || '');
      $('#userPhone').val(userData.userPhone || '');

      // IMPORTANT: use cityCode, not city text
      $('#userCity').val(userData.cityCode || '').trigger('change');

      $('#acceptOffers').prop('checked', !!userData.acceptOffers);

      // ===== DISABLE EVERYTHING =====
      $('.paxDetails, .contact').find('input, select, textarea, button').prop('disabled', true);
   }
   function setFormDisabled(disabled) {
      $('.paxDetails, .contact').find('input, select, textarea, button').prop('disabled', disabled);
   }
   // Skip directly to ticket page (final screen) ////////////////////////ALLLLLLLLLLLLIIIIIIIIIIIIIIIIIIIIII::::::::::::::)))))))))))))
   // showTicketPage();
   // Skip directly to Payment page (final screen) ////////////////////////ALLLLLLLLLLLLIIIIIIIIIIIIIIIIIIIIII::::::::::::::)))))))))))))
   // showPaymentPage();
</script>
@if (isset($data) && !empty($data))
   @if ($isEmirate)
      <script>
         let data = @json($data);
         let firstBtn = true;
         $(".next").click(async function () {
            let current_fs = $(this).parent();
            let next_fs = current_fs.next();

            if (!validateFields(current_fs)) return;
            try {
               await verifyClient();
               if (firstBtn) {
                  confirmationModal('Please confirm that all the provided details are correct.').then((result) => {
                     if (result.isConfirmed) {
                        $('#paymentSendTest').addClass('d-none');
                        // firstBtn = false;
                        bookingAjax(current_fs, next_fs, firstBtn); // Pass current_fs and next_fs to bookingAjax
                     } else {
                        _alert('Confirmation cancelled.', 'warning');
                     }
                  });
               } else {
                  let index = $("fieldset").index(next_fs);
                  $("#progressbar li").eq(index).addClass("active");
                  next_fs.show();
                  current_fs.animate({ opacity: 0 }, {
                     step: (now) => {
                        current_fs.css({ 'display': 'none', 'position': 'relative' });
                        next_fs.css({ 'opacity': 1 - now });
                     },
                     duration: 500
                  });
                  setProgressBar(++current);
               }
               // console.log('await');
            } catch (e) {
               // console.log('catch')
               return;
            }
            // console.log('newxt');

         });
         $(".submit").click(() => false);
         setProgressBar(current);
         $(document).on("input change", "input[required], select[required]", function () {
            if ($(this).val()) {
               $(this).removeClass("border-danger");
            }
         });
         // ------------------------------------ Booking Start ------------------------------------ //
         const renderTravelerDetails = (data, tickets) => {
            if (!Array.isArray(data) || data.length === 0) {
               return `<div class="alert alert-danger" role="alert">Data is missing :)</div>`;
            }
            return data.map((passenger, index) => {
               const matchingTicket = (tickets || []).find(t => t.passenger_reference === passenger.passenger_reference);

               const ticketHtml = matchingTicket ? `
                  <div class="col-6">
                     <div class="border rounded p-3 mt-2">
                        <p>Issue date: <br><span>${formatDateTime(matchingTicket.issue_date)}</span></p>
                        <p>ETicket No: <br><span class="copyText">${matchingTicket.ticket_no}</span></p>
                        <p>Type: <span>E-Ticket</span></p>
                        <p>Price Reference: <span>${matchingTicket.price_reference}</span></p>
                     </div>
                  </div>
               ` : '<div class="col-12">No Ticket Issued</div>';
               return `
                  <div class="custom-method setp-bult traveler-bult row">
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Passenger Details</h1>
                        <p><span>Traveler ${index + 1}</span></p>
                        <p><span>Title</span>: ${passenger.title || ''}</p>
                        <p><span>Name</span>: ${passenger.given_name || passenger.name}</p>
                        <p><span>Surname</span>: ${passenger.surname || passenger.surName}</p>
                     </div>
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Ticket Details</h1>
                        <div class="row">
                           ${ticketHtml}
                        </div>
                     </div>
                  </div>
               `;
            }).join('');
         };
         const renderPaxWithPrice = data => {
            if (data.length === 0) return ``;
            return data.map((row) => `
               <div class="pri-eid">
                  <p>Emirates Airline - (${row.passenger_code})</p>
                  <p>Price: ${row.price_code} ${formatCurrency(row.price)}</p>
               </div>
            `).join('');
         };
         const renderTimeLimitsEmi = data => {
            if (data.length === 0) return ``;
            const payTimeLimit = formatDateTime(data.payment_limit);
            const ticketTimeLimit = formatDateTime(data.ticket_limit);
            return `
               <div class="pri-eid font-weight-bold">
                  <p>Payment Time Limit</p>
                  <p class="font-bold">${payTimeLimit}</p>
               </div>
               <div class="pri-eid font-weight-bold">
                  <p>Ticket Time Limit</p>
                  <p class="font-bold">${ticketTimeLimit}</p>
               </div>
            `;
         };
         const renderTaxDetailsEmi = data => {
            if (data.length === 0) return ``;
            return data.map((row) => {
               const taxes = JSON.parse(row.taxes);
               const baseAmount = formatCurrency(taxes.baseAmount.amount);
               const baseAmountCode = taxes.baseAmount.code;
               const taxArray = taxes.tax;
               const totalPrice = formatCurrency(row.price);
               const totalPriceCode = row.price_code;
               const passengers = row.passenger_code;
               const taxDetails = taxArray.map(tax => {
                  if (tax.description && tax.description.length > 0) {
                     return `
                        <div class="pri-eid">
                           <p>${tax.description}</p>
                           <p>Price: ${tax.price.code} ${formatCurrency(tax.price.amount)}</p>
                        </div>
                     `;
                  }
                  return '';
               }).join('');
               return `
                  <div class="pri-eid font-weight-bold">
                     <h1 class="text-info">Passenger Info: ${passengers}</h1>
                  </div>
                  <div class="pri-eid font-weight-bold">
                     <p>Base Amount</p>
                     <p class="font-bold">Price: ${baseAmountCode} ${baseAmount}</p>
                  </div>
                  ${taxDetails}
                  <div class="pri-eid font-weight-bold">
                     <p>Final Amount ${passengers}</p>
                     <p class="font-bold">Price: ${totalPriceCode} ${totalPrice}</p>
                  </div>
               `;
            }).join('');
         };
         const renderServiceDetailsEmi = data => {
            if (!data || data.length === 0) return ``;
            return data.map(row => {
               const passengers = row.passenger_code;
               const serviceArray = JSON.parse(row.services || null);
               const serviceDetails = serviceArray.map(service => {
                     if (service.details && service.details.Type && service.details.details?.length > 0) {
                        return `
                           <div class="pri-eid">
                              <p class="font-weight-bold">${service.details.details}</p>
                              <p>${service.details.Type}</p>
                           </div>`;
                     }
                     return '';
               }).join('');
               if (!serviceDetails.trim()) return '';
               return `
                  <div class="pri-eid font-weight-bold">
                     <h5 class="text-info">Passenger Info: ${passengers}</h5>
                  </div>
                  ${serviceDetails}
               `;
            }).join('');
         };
         function setTicketPage (response) {
            let totalPrice = parseInt(response.booking?.price) + parseInt(tax);
            $(".totalPricePaid").text(`Price: ${response.booking?.price_code ?? 'PKR'} ${formatCurrency(totalPrice) ?? 0}`);
            $(".taxPaid").text(`Price: PKR ${formatCurrency(tax)}`);
            $(".guestName").text(response.booking.client.name);
            $(".ticketMsg").text(response.message);
            $(".contactDetails").html(renderTravelerDetails(JSON.parse(response.booking?.passenger_details), (response.booking?.tickets || []) ));
            $(".paxWithPrice").html(renderPaxWithPrice(response.booking?.booking_items));
            $(".emiTimeLimitContainer").removeClass('d-none');
            $(".timeLimitsEmi").html(renderTimeLimitsEmi(response.booking));
            $(".emiTaxContainer").removeClass('d-none');
            $(".emiServiceContainer").removeClass('d-none');
            $(".taxDetailsEmi").html(renderTaxDetailsEmi(response.booking?.booking_items));
            $(".serviceDetailsEmi").html(renderServiceDetailsEmi(response.booking?.booking_items));
            $(".orderId").html(response.booking.order_id);
         }
         // function showOnHoldBooking(){
         //    let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
         //    let approvePayCash = JSON.parse((localStorage.getItem('approvePayCash') || false));
         //    let ticketIssued = JSON.parse((localStorage.getItem('ticketIssued') || false));
            
         //    if (createdBooking) {
         //       orderId = createdBooking.booking.order_id;
         //       showPaymentPage();
         //    }
         //    if(createdBooking && (approvePayCash || ticketIssued)) {
         //       sessionTimer(false);
         //       setTicketPage(createdBooking);
         //       showTicketPage();
         //    }
         // }
         // showOnHoldBooking();
         function bookingAjax(current_fs, next_fs, firstBtn) {
            let user = getUserDetails();
            $.ajax({
               type: "POST",
               url: "{{route('bookFlight')}}",
               data: {
                  user, passengers,
                  airline: data['airline'],
                  offerIds: getOfferIds(data['flightDetails']['bundle']['offerItem']) ?? null,
                  bundleId: data['flightDetails']['bundle']['offerID'] ?? null,
                  responseId: data['flightDetails']['segments'][0]['responseId'] ?? null,
                  paxCount: data['paxCount'] ?? null,
                  passengerTypes: data['passengerTypes'] ?? null,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  $('#paymentSendTest').removeClass('d-none');
                  localStorage.setItem('booking', JSON.stringify(response));
                  showPaymentPage();

                  // Move UI updates here to ensure they run only on success
                  firstBtn = false;
                  let index = $("fieldset").index(next_fs);
                  $("#progressbar li").eq(index).addClass("active");
                  next_fs.show();
                  current_fs.animate({ opacity: 0 }, {
                     step: (now) => {
                        current_fs.css({ 'display': 'none', 'position': 'relative' });
                        next_fs.css({ 'opacity': 1 - now });
                     },
                     duration: 500
                  });
                  setProgressBar(++current);
               },
               error: function (xhr) {
                  firstBtn = true;
                  console.log(xhr);
                  (async () => {
                     let alMsg = xhr.responseJSON?.details?.value || 'Please check your details, something seems incorrect.';
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        // let goBack = localStorage.getItem('flights') || null;
                        // goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
                  // _alert(xhr.responseJSON.message || 'Booking Error', "error");
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
         function approveBookingAjax() {
            showOnHoldBooking();
            let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
            if (!createdBooking.booking.id || !createdBooking.booking.client_id) return showMissingDataMsg();
            $.ajax({
               type: "POST",
               url: "{{route('confirm.booking')}}",
               data: {
                  bookingId: createdBooking.booking.id,
                  clientId: createdBooking.booking.client_id,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  localStorage.setItem('ticketIssued', JSON.stringify(true));
                  localStorage.setItem('booking', JSON.stringify(response));
                  (async () => {
                     let alMsg = 'Your payment was successful. Ticket details are shown below and will also be sent to your email.';
                     if (await _confirm(alMsg, false, 'success', 'Continue')) {
                        _alert(response.message)
                        setTicketPage(response);
                        showTicketPage();
                        sessionTimer(false);
                     }
                  })();
               },
               error: function (xhr) {
                  (async () => {
                     let phone = "{{ config('variables.contact.phone') }}";
                     let alMsg = `Ticket issue error. please contact us at ${phone} your Order Id is ${orderId ?? 'N/A'}.`;
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        let goBack = localStorage.getItem('flights') || null;
                        goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
         // ------------------------------------ Booking End ------------------------------------ //
         // -------------------------------- Combine Functions :) -------------------------------- //
         const getOfferIds = data =>
            (Array.isArray(data) ? data : data ? [data] : []).map(item => ({
                  id: item?.id || null,
                  PassengerRef: item?.services?.[0]?.passengerRefs || null
            }));
      </script>
   @elseif ($isFlyJinnah)
      <script>
         // ---------------------------------- /////////////////////////////////////---------FLYJINNAH-------------\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ ----------------------------------
         $(document).ready(function () {
            let rawData = @json($data);
            let data = rawData;
            let totalFare = @json($totalFare);
            let isDirectBooking = @json($data['isDirectBooking']) ? true : false;
            let firstBtn = true;
            function getSegmentAttributes(flightNo) {
               let segmenArry = data['segments'][0] ? data['segments'] : [data['segments']];
               return segmenArry.find(s => s.flightNumber === flightNo);
            }
            $(".next").click(async function () {
               let current_fs = $(this).parent();
               let next_fs = current_fs.next();

               if (!validateFields(current_fs)) return;
               if (!isDirectBooking && !checkValidationForAncis()) return;

               try {
                  await verifyClient();
                  if (firstBtn) {
                     confirmationModal('Please confirm that all the provided details are correct.').then((result) => {
                        if (result.isConfirmed) {
                           $('#paymentSendTest').addClass('d-none');
                           // firstBtn = false;
                           bookingAjax(current_fs, next_fs, firstBtn); // Pass current_fs and next_fs to bookingAjax
                        } else {
                           _alert('Confirmation cancelled.', 'warning');
                        }
                     });
                  } else {
                     let index = $("fieldset").index(next_fs);
                     $("#progressbar li").eq(index).addClass("active");
                     next_fs.show();
                     current_fs.animate({ opacity: 0 }, {
                        step: (now) => {
                           current_fs.css({ 'display': 'none', 'position': 'relative' });
                           next_fs.css({ 'opacity': 1 - now });
                        },
                        duration: 500
                     });
                     setProgressBar(++current);
                  }
               } catch (e) {
                  return;
               }
            });
            $(".submit").click(() => false);
            setProgressBar(current);
            $(document).on("input change", "input[required], select[required]", function () {
               if ($(this).val()) {
                  $(this).removeClass("border-danger");
               }
            });

            // ------------------------------------ Booking Start ------------------------------------ //
            let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
            if (!isDirectBooking && !createdBooking) {
               getSeatAjax();
               getMealAjax();
               getBaggageAjax();
            }
            // if(!isDirectBooking) {
            // }
            // IdsExpireTime end
            // let passengers = [];
            // let paymentdata;
            let passengerList = [];
            let passengerListCode = [];
            delete data['passengerTypes']['inf'];
            delete data['paxCount']['inf'];
            let totalPassengers = Object.values(data['paxCount']).reduce((a, b) => a + parseInt(b), 0);
            let passengerTypeMap = {
               adt: 'A',
               chd: 'C',
            };
            let runningIndex = 1;
            $.each(data['paxCount'], function (key, count) {
               // if (key === 'inf') return;
               let passengerTypeInitial = passengerTypeMap[key] || key.charAt(0).toUpperCase();
               for (let i = 0; i < parseInt(count); i++) {
                  passengerListCode.push(`${passengerTypeInitial}${runningIndex}`);
                  runningIndex++;
               }
            });
            // $.each(data['paxCount'], function (key, count) {
               //   for (let i = 1; i <= parseInt(count); i++) {
               //      passengerListCode.push(`${data['passengerTypes'][key][0]}${i}`);
               //   }
            // });
            $.each(data['paxCount'], function (key, count) {
               // if (key === 'inf') return;
               for (let i = 1; i <= parseInt(count); i++) {
                  passengerList.push(`${data['passengerTypes'][key]} - ${i}`);
               }
            });
            // console.log(passengerListCode, passengerList, data['passengerTypes'], totalPassengers)
            let selectedSeatsGlobal = {};
            let selectedMealsGlobal = {};
            let selectedBaggagesGlobal = {};
            let segmentRph = {};
            let segmentFlightNo = {};
            let segmentDepDate = {};

            function getSeatAjax() {
               $.ajax({
                  type: "POST",
                  url: "{{route('get_seat')}}",
                  data: {
                     data: data['segments'], 
                     _token: "{{ csrf_token() }}"
                  },
                  beforeSend: () => _loader('show'),
                  success: function (response) {
                     if (response.status === 'success') {
                        $(".seatFlightSegments, .segmentNavTabSeat, .segmentBtnsSeat").empty();
                        $(".infantCountSeatBtn").empty();

                        let paxSeatBtns = `
                           <div class="border-bottom border-dark mt-2 p-2">
                              <div class="overflow-auto infantCountSeatBtn">${passengerList.map(pax => `<a class="btn btn-outline-info mx-2 pax-btn" data-pax="${pax}">${pax} <span class="seatNo"></span></a>`).join('')}</div>
                              <hr class="bg-dark">
                              <div class="d-flex justify-content-between">
                                 <h2 class="font-weight-bold">Total fare:</h2>
                                 <p class="totalPriceOfSeat">PKR 0</p>
                              </div>
                           </div>`;

                        let navTabs = `<ul class="nav nav-tabs segmentNavTabSeat">`;
                        let tabContent = `<div class="tab-content segmentBtnsSeat planeParent mt-3">${paxSeatBtns}`;

                        $.each(response.data, function (index, item) {
                           let rph = getSegmentAttributes(item.FlightSegmentInfo["@attributes"].FlightNumber).rph || item.FlightSegmentInfo["@attributes"].RPH;
                           segmentRph[`flight-${index}`] = rph;
                           segmentFlightNo[`flight-${index}`] = item.FlightSegmentInfo["@attributes"].FlightNumber;
                           segmentDepDate[`flight-${index}`] = item.FlightSegmentInfo["@attributes"].DepartureDateTime;
                           let departureCity = item['FlightSegmentInfo']['DepartureAirport']['City'] || item['FlightSegmentInfo']['DepartureAirport']['@attributes']['LocationCode'] || '--';
                           let arrivalCity = item['FlightSegmentInfo']['ArrivalAirport']['City'] || item['FlightSegmentInfo']['ArrivalAirport']['@attributes']['LocationCode'] || '--';

                           let tabId = `flight-${index}`;
                           selectedSeatsGlobal[tabId] = selectedSeatsGlobal[tabId] || [];

                           navTabs += `<li class="nav-item">
                              <a class="nav-link ${index === 0 ? 'active' : ''}" data-toggle="tab" href="#${tabId}">
                                 ${departureCity}-${arrivalCity}
                              </a>
                           </li>`;

                           tabContent += `<div id="${tabId}" class="tab-pane tabPlane fade ${index === 0 ? 'show active' : ''}">
                              <div class="winds1"><div class="winds2"><div class="windows"><div class="windows2"><div class="seatMapContainer d-flex"></div></div></div></div></div>
                           </div>`;
                        });

                        navTabs += `</ul>`;
                        tabContent += `</div>`;

                        $(".seatFlightSegments").append(navTabs);
                        $(".plane").append(tabContent);

                        loadSeatMap("flight-0", response.data[0].SeatMapDetails.CabinClass, totalPassengers, passengerList);

                        $('.segmentNavTabSeat a').click(function (e) {
                           e.preventDefault();
                           $(this).tab('show');

                           let tabId = $(this).attr("href").replace("#", "");
                           let index = tabId.replace("flight-", "");
                           let tabContainer = $("#" + tabId).find(".seatMapContainer");

                           if (!tabContainer.hasClass("loaded")) {
                              tabContainer.addClass("loaded");
                              loadSeatMap(tabId, response.data[index].SeatMapDetails.CabinClass, totalPassengers, passengerList);
                           }

                           updatePassengerSeats(tabId);
                        });
                     }
                  },
                  error: function (xhr) {
                     console.log(xhr.responseJSON.message);
                     showMissingDataMsg();
                     // _alert(xhr.responseJSON.message, "error");
                  },
                  complete: function () {
                     _loader('hide');
                  }
               });
            };
            function loadSeatMap(flightId, seatData, totalPassengers, passengerList) {
               let container = $("#" + flightId + " .seatMapContainer");
               container.empty();
               let selectedSeats = selectedSeatsGlobal[flightId] || [];

               if (Array.isArray(seatData)) {
                  seatData.forEach(cabin => renderSeatMap(cabin.AirRows.AirRow, container, totalPassengers, passengerList, flightId));
               } else {
                  renderSeatMap(seatData.AirRows.AirRow, container, totalPassengers, passengerList, flightId);
               }
            }
            function updatePassengerSeats(flightId) {
               $(".infantCountSeatBtn .seatNo").text(""); // Reset previous seats

               let selectedSeats = selectedSeatsGlobal[flightId] || [];
               selectedSeats.forEach((seat, index) => {
                  $(".infantCountSeatBtn .seatNo").eq(index).text(`(${seat.seatId})`);
               });

               let totalPrice = selectedSeats.reduce((total, seat) => total + parseFloat(seat.price), 0);
               $(".totalPriceOfSeat").text("PKR " + totalPrice);
            }
            function renderSeatMap(airRows, container, totalPassengers, passengerList, flightId) {
               let selectedSeats = selectedSeatsGlobal[flightId] || [];
               let rph = segmentRph[flightId];
               let flightNo = segmentFlightNo[flightId];
               let depDate = segmentDepDate[flightId];

               airRows.forEach(row => {
                  let rowNumber = row['@attributes']['RowNumber'];
                  let seats = row['AirSeats']['AirSeat'];

                  let seatRow = $("<div>").addClass("seat-row");

                  seats.forEach((seatObj, i) => {
                     if (i === 3) {
                        seatRow.append($("<div>").addClass("aisle"));
                     }

                     let attr = seatObj['@attributes'];
                     let seatId = rowNumber + attr['SeatNumber'];
                     let price = attr['SeatCharacteristics'];
                     let currency = attr['CurrencyCode'];
                     let availability = attr['SeatAvailability'];

                     let seat = $("<div>").addClass("seat").text(seatId).attr({
                        "data-seat": seatId,
                        "data-price": price,
                        "data-currency": currency,
                        "title": `${currency} ${price}`
                     });

                     if (availability !== 'VAC') {
                        seat.addClass("occupied");
                     }

                     // Restore previously selected seats
                     if (selectedSeats.some(s => s.seatId === seatId)) {
                        seat.addClass("selected");
                     }

                     seat.click(function () {
                        if ($(this).hasClass("occupied")) return;

                        let selectedForFlight = selectedSeatsGlobal[flightId] || [];

                        if ($(this).hasClass("selected")) {
                           $(this).removeClass("selected");
                           selectedSeats = selectedSeats.filter(s => s.seatId !== seatId);
                           selectedSeatsGlobal[flightId] = selectedSeats;
                        } else {
                           if (selectedForFlight.length >= totalPassengers) {
                              return;
                           }

                           let passenger = passengerListCode[selectedForFlight.length];
                           $(this).addClass("selected");
                           selectedSeats.push({ seatId, price, currency, passenger, rph, flightNo, depDate });
                           selectedSeatsGlobal[flightId] = selectedSeats;
                        }

                        updatePassengerSeats(flightId);
                     });

                     seatRow.append(seat);
                  });

                  container.append(seatRow);
               });

               selectedSeatsGlobal[flightId] = selectedSeats;
            }

            function getMealAjax() {
               $.ajax({
                  type: "POST",
                  url: "{{route('get_meal')}}",
                  data: {
                     data: data['segments'], 
                     _token: "{{ csrf_token() }}"
                  },
                  success: function (response) {
                     if (response.status === 'success') {
                        $(".mealFlightSegments, .segmentNavTabMeal, .segmentBtnsMeal").empty();
                        $(".infantCountMealBtn").empty();
                        // Create passenger buttons
                        let paxMealBtns = `
                           <div class="border-bottom border-dark mt-2 p-2">
                                 <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5>Select Meals for Passengers:</h5>
                                    <button type="button" class="btn btn-sm btn-primary select-same-meal">Select Same Meal for All</button>
                                 </div>
                                 <div class="overflow-auto infantCountMealBtn">
                                    ${passengerList.map((pax, index) => `
                                       <a class="btn btn-outline-info mx-2 pax-btn" data-pax="${pax}" data-pax-index="${index}">
                                             ${pax} 
                                             <span class="mealNo"></span>
                                       </a>
                                    `).join('')}
                                 </div>
                                 <hr class="bg-dark">
                                 <div class="d-flex justify-content-between">
                                    <h2 class="font-weight-bold">Total fare:</h2>
                                    <p class="totalPriceOfMeal">PKR 0</p>
                                 </div>
                           </div>`;

                        let navTabs = `<ul class="nav nav-tabs segmentNavTabMeal">`;
                        let tabContent = `<div class="tab-content segmentBtnsMeal mt-3">${paxMealBtns}`;

                        $.each(response.data, function (index, item) {
                           let rph = getSegmentAttributes(item.FlightSegmentInfo["@attributes"].FlightNumber).rph || item.FlightSegmentInfo["@attributes"].RPH;
                           segmentRph[`mealFlight-${index}`] = rph;
                           segmentFlightNo[`mealFlight-${index}`] = item.FlightSegmentInfo["@attributes"].FlightNumber;
                           segmentDepDate[`mealFlight-${index}`] = item.FlightSegmentInfo["@attributes"].DepartureDateTime;

                           let departureCity = item.FlightSegmentInfo.DepartureAirport.City || item.FlightSegmentInfo.DepartureAirport["@attributes"].LocationCode || '--';
                           let arrivalCity = item.FlightSegmentInfo.ArrivalAirport.City || item.FlightSegmentInfo.ArrivalAirport["@attributes"].LocationCode || '--';

                           let tabId = `mealFlight-${index}`;
                           selectedMealsGlobal[tabId] = selectedMealsGlobal[tabId] || {};

                           navTabs += `
                                 <li class="nav-item">
                                    <a class="nav-link ${index === 0 ? 'active' : ''}" data-toggle="tab" href="#${tabId}">
                                       ${departureCity}-${arrivalCity}
                                    </a>
                                 </li>`;

                           tabContent += `
                                 <div id="${tabId}" class="tab-pane fade ${index === 0 ? 'show active' : ''}">
                                    <div class="mealMapContainer row my-2"></div>
                                 </div>`;
                        });
                        navTabs += `</ul>`;
                        tabContent += `</div>`;

                        $(".mealFlightSegments").append(navTabs);
                        $(".meals").append(tabContent);

                        // Initialize current passenger selection
                        currentSelectedPax = 0;
                        $(".pax-btn").eq(0).addClass("active");

                        // Load Meal Map for the first segment
                        loadMealMap("mealFlight-0", response.data[0].Meal, totalPassengers, passengerList);

                        // Tab Click Handling
                        $('.segmentNavTabMeal a').click(function (e) {
                           e.preventDefault();
                           $(this).tab('show');

                           let tabId = $(this).attr("href").replace("#", "");
                           let index = tabId.split("-")[1];

                           if (!Object.keys(selectedMealsGlobal[tabId]).length) {
                              loadMealMap(tabId, response.data[index].Meal, totalPassengers, passengerList);
                           }

                           updatePassengerMeals(tabId);
                        });

                        // Passenger button click handler
                        $(".pax-btn").click(function() {
                           $(".pax-btn").removeClass("active");
                           $(this).addClass("active");
                           currentSelectedPax = $(this).data("pax-index");
                        });

                        // Select same meal for all button
                        $(".select-same-meal").click(function() {
                           let activeTab = $(".segmentNavTabMeal .nav-link.active").attr("href").replace("#", "");
                           let selectedMealCard = $(`#${activeTab} .mealCard.selected`);
                           
                           if (selectedMealCard.length) {
                                 let mealName = selectedMealCard.data("meal");
                                 let mealPrice = selectedMealCard.data("price");
                                 let mealCode = selectedMealCard.data("meal-code");
                                 let rph = segmentRph[activeTab];
                                 let flightNo = segmentFlightNo[activeTab];
                                 let depDate = segmentDepDate[activeTab];
                                 
                                 $(`#${activeTab} .mealCard`).removeClass("selected");
                                 selectedMealsGlobal[activeTab] = {};
                                 
                                 passengerListCode.forEach((pax, index) => {
                                    selectedMealsGlobal[activeTab][index] = {
                                       mealName,
                                       mealPrice,
                                       mealCode,
                                       passenger: pax,
                                       rph: rph,
                                       flightNo: flightNo,
                                       depDate: depDate
                                    };
                                 });
                                 
                                 $(`#${activeTab} .mealCard[data-meal="${mealName}"]`).addClass("selected");
                                 updatePassengerMeals(activeTab);
                           } else {
                                 _alert("Please select a meal first", "warning");
                           }
                        });
                     }
                  },
                  error: function (xhr) {
                     console.log(xhr.responseJSON.message);
                     showMissingDataMsg();
                  },
                  complete: function () {
                     // _loader('hide');
                  }
               });
            };
            function loadMealMap(flightId, mealData, totalPassengers, passengerList) {
               let container = $("#" + flightId + " .mealMapContainer");
               container.empty();

               let mealsArray = Array.isArray(mealData) ? mealData : [mealData];
               let selectedMeals = selectedMealsGlobal[flightId] || {};
               let html = ``;

               mealsArray.forEach(meal => {
                  let mealImg = '/assets/images/mealdefault.jpg';
                  let isSelected = Object.values(selectedMeals).some(m => m.mealName === meal.mealName) ? 'selected' : '';

                  html += `
                        <div class="col-sm-12  col-md-4 col-lg-3  p-2 border mealCard pointer ${isSelected}" data-meal="${meal.mealName}" data-price="${meal.currencyCode} ${meal.mealCharge}" data-meal-code="${meal.mealCode}">
                           <img src="${mealImg}" alt="" class="img-thumbnail">
                           <div class="meal-head d-flex justify-content-between">
                              <p class="font-weight-bolder">Meal Name</p>
                              <p>${meal.mealName}</p>
                           </div>
                           <div class="meal-head d-flex justify-content-between">
                              <p class="font-weight-bolder">Meal Charge</p>
                              <p>${meal.currencyCode} ${meal.mealCharge}</p>
                           </div>
                        </div>`;
               });

               container.append(html);

               // Meal selection event
               $(".mealCard").click(function () {
                  let mealName = $(this).data("meal");
                  let mealPrice = $(this).data("price");
                  let mealCode = $(this).data("meal-code");
                  let flightId = $(this).closest(".tab-pane").attr("id");
                  let selectedMeals = selectedMealsGlobal[flightId] || {};
                  let currentPax = currentSelectedPax;
                  let rph = segmentRph[flightId];
                  let flightNo = segmentFlightNo[flightId];
                  let depDate = segmentDepDate[flightId];

                  if ($(this).hasClass("selected") && selectedMeals[currentPax]?.mealName === mealName) {
                        $(this).removeClass("selected");
                        delete selectedMeals[currentPax];
                  } else {
                        $(this).addClass("selected");
                        selectedMeals[currentPax] = {
                           mealName,
                           mealPrice,
                           mealCode,
                           passenger: passengerListCode[currentPax],
                           rph: rph,
                           flightNo: flightNo,
                           depDate: depDate
                        };
                  }

                  selectedMealsGlobal[flightId] = selectedMeals;
                  updatePassengerMeals(flightId);
               });
            }
            function updatePassengerMeals(flightId) {
               $(".infantCountMealBtn .mealNo").text("");

               let selectedMeals = selectedMealsGlobal[flightId] || {};
               Object.entries(selectedMeals).forEach(([index, meal]) => {
                  $(".infantCountMealBtn .mealNo").eq(index).text(`(${meal.mealName})`);
               });

               let totalPrice = Object.values(selectedMeals).reduce((total, meal) => {
                  return total + parseFloat(meal.mealPrice.replace(/[^\d.]/g, ''));
               }, 0);
               $(".totalPriceOfMeal").text("PKR " + totalPrice);
            }

            function getBaggageAjax() {
               $.post("{{route('get_baggage')}}", { data: data['segments'], _token: "{{ csrf_token() }}" }, response => {
                  if (response.status !== 'success') return _alert(response.message, "error");

                  $(".baggageFlightSegments, .segmentNavTabBaggage, .segmentBtnsBaggage, .infantCountBaggageBtn").empty();
                  let passengerButtons = `
                        <div class="border-bottom border-dark mt-2 p-2">
                           <div class="d-flex justify-content-between align-items-center mb-3">
                              <h5>Select Baggage for Passengers:</h5>
                              <button type="button" class="btn btn-sm btn-primary select-same-baggage">Select Same Baggage for All</button>
                           </div>
                           <div class="overflow-auto infantCountBaggageBtn">
                              ${passengerList.map((pax, index) => `
                                    <a class="btn btn-outline-info mx-2 pax-btn" data-pax="${pax}" data-pax-index="${index}">
                                       ${pax} 
                                       <span class="BaggageNo"></span>
                                    </a>
                              `).join('')}
                           </div>
                           <hr class="bg-dark">
                           <div class="d-flex justify-content-between">
                              <h2 class="font-weight-bold">Total fare:</h2>
                              <p class="totalPriceOfBaggage">PKR 0</p>
                           </div>
                        </div>`;

                  let navTabs = `<ul class="nav nav-tabs segmentNavTabBaggage">`;
                  let tabContent = `<div class="tab-content segmentBtnsBaggage mt-3">${passengerButtons}`;
                  response.data.forEach((item, index) => {
                     let segmentInfo = Array.isArray(item.OnDFlightSegmentInfo)
                        ? item.OnDFlightSegmentInfo
                        : [item.OnDFlightSegmentInfo];

                     if (!segmentInfo || segmentInfo.length === 0) return;

                     // Prepare combined info for all segments in the flight
                     let flightSegments = segmentInfo.map((seg, segIndex) => {
                        const attrs = seg["@attributes"];
                        return {
                           rph: getSegmentAttributes(attrs.FlightNumber).rph || attrs.RPH,
                           flightNo: attrs.FlightNumber,
                           depDate: attrs.DepartureDateTime
                        };
                     });

                     let tabId = `baggageFlight-${index}`;
                     segmentRph[tabId] = flightSegments.map(seg => seg.rph);
                     segmentFlightNo[tabId] = flightSegments.map(seg => seg.flightNo);
                     segmentDepDate[tabId] = flightSegments.map(seg => seg.depDate);

                     let firstSeg = segmentInfo[0];
                     let lastSeg = segmentInfo[segmentInfo.length - 1];

                     let departureCity = getCity(firstSeg.DepartureAirport);
                     let arrivalCity = getCity(lastSeg.ArrivalAirport);

                     selectedBaggagesGlobal[tabId] = selectedBaggagesGlobal[tabId] || {};

                     navTabs += `
                        <li class="nav-item">
                           <a class="nav-link ${index === 0 ? 'active' : ''}" data-toggle="tab" href="#${tabId}">
                              ${departureCity}-${arrivalCity}
                           </a>
                        </li>`;

                     tabContent += `
                        <div id="${tabId}" class="tab-pane fade ${index === 0 ? 'show active' : ''}">
                           <div class="baggageMapContainer row my-2"></div>
                        </div>`;

                     loadBaggageMap(tabId, item.Baggage, totalPassengers, passengerList);
                  });
                  tabContent += `</div>`;

                  $(".baggageFlightSegments").append(navTabs + `</ul>`);
                  $(".baggages").append(tabContent + `</div>`);

                  currentSelectedPaxBaggage = 0;
                  $(".segmentBtnsBaggage .pax-btn").eq(0).addClass("active");
                  loadBaggageMap("baggageFlight-0", response.data[0].Baggage, totalPassengers, passengerList);
                  $('.segmentNavTabBaggage a').click(function (e) {
                     e.preventDefault();
                     $(this).tab('show');
                     let tabId = $(this).attr("href").slice(1);
                     if (!Object.keys(selectedBaggagesGlobal[tabId]).length) {
                        loadBaggageMap(tabId, response.data[tabId.split("-")[1]].Baggage, totalPassengers, passengerList);
                     }
                     updatePassengerBaggages(tabId);
                  });

                  $(".segmentBtnsBaggage .pax-btn").click(function() {
                     $(".segmentBtnsBaggage .pax-btn").removeClass("active");
                     $(this).addClass("active");
                     currentSelectedPaxBaggage = $(this).data("pax-index");
                  });

                  $(".select-same-baggage").click(function() {
                     let activeTab = $(".segmentNavTabBaggage .nav-link.active").attr("href").replace("#", "");
                     let selectedBaggageCard = $(`#${activeTab} .baggageCard.selected`);
                     if (!selectedBaggageCard.length) {
                        _alert("Please select a baggage option first", "warning");
                        return;
                     }
                     let baggageDescription = selectedBaggageCard.data("description");
                     let baggagePrice = selectedBaggageCard.data("price");
                     let baggageCode = selectedBaggageCard.data("baggage-code");
                     let rph = segmentRph[activeTab];
                     let flightNo = segmentFlightNo[activeTab];
                     let depDate = segmentDepDate[activeTab];
                     $(`#${activeTab} .baggageCard`).removeClass("selected");
                     selectedBaggagesGlobal[activeTab] = {};
                     passengerListCode.forEach((pax, index) => {
                        selectedBaggagesGlobal[activeTab][index] = {
                           baggageDescription,
                           baggagePrice,
                           baggageCode,
                           passenger: pax,
                           rph: rph,
                           flightNo: flightNo,
                           depDate: depDate
                        };
                     });
                     
                     $(`#${activeTab} .baggageCard[data-description="${baggageDescription}"]`).addClass("selected");
                     updatePassengerBaggages(activeTab);
                  });

               }).fail(xhr => showMissingDataMsg())
               // .always(() => _loader('hide'));
               .always();
            };
            const loadBaggageMap = (flightId, baggageData, totalPassengers, passengerList) => {
               let container = $(`#${flightId} .baggageMapContainer`).empty();
               let baggagesArray = Array.isArray(baggageData) ? baggageData : [baggageData];
               let selectedBaggages = selectedBaggagesGlobal[flightId] || {};

               let html = baggagesArray.map(baggage => {
                  // Check if this baggage is selected for any passenger
                  let isSelected = Object.values(selectedBaggages).some(b => b.baggageDescription === baggage.baggageDescription) ? 'selected' : '';
                  
                  return `<div class="col-sm-12  col-md-4 col-lg-3 p-2 border baggageCard pointer ${isSelected}" 
                              data-description="${baggage.baggageDescription}" 
                              data-price="${baggage.currencyCode} ${baggage.baggageCharge}" 
                              data-baggage-code="${baggage.baggageCode}">
                           <img src="/assets/images/baggagedefault.jpg" alt="" class="img-thumbnail">
                           <div class="meal-head d-flex justify-content-between">
                              <p class="font-weight-bolder">${baggage.baggageDescription}</p>
                           </div>
                           <div class="meal-head d-flex justify-content-between">
                              <p class="font-weight-bolder">${baggage.currencyCode} ${baggage.baggageCharge}</p>
                           </div>
                        </div>`;
               }).join('');

               container.append(html);

               $(".baggageCard").click(function () {
                  let baggageDescription = $(this).data("description");
                  let baggagePrice = $(this).data("price");
                  let baggageCode = $(this).data("baggage-code");
                  let flightId = $(this).closest(".tab-pane").attr("id");
                  let selectedBaggages = selectedBaggagesGlobal[flightId] || {};
                  let currentPax = currentSelectedPaxBaggage;
                  let rph = segmentRph[flightId];
                  let flightNo = segmentFlightNo[flightId];
                  let depDate = segmentDepDate[flightId];

                  if ($(this).hasClass("selected") && selectedBaggages[currentPax]?.baggageDescription === baggageDescription) {
                     $(this).removeClass("selected");
                     delete selectedBaggages[currentPax];
                  } else {
                     $(this).addClass("selected");
                     selectedBaggages[currentPax] = {
                        baggageDescription,
                        baggagePrice,
                        baggageCode,
                        passenger: passengerListCode[currentPax],
                        rph: rph,
                        flightNo: flightNo,
                        depDate: depDate
                     };
                  }

                  selectedBaggagesGlobal[flightId] = selectedBaggages;
                  updatePassengerBaggages(flightId);
               });
            }
            function updatePassengerBaggages(tabId) {
               $(".infantCountBaggageBtn .BaggageNo").text("");
               let selectedForTab = selectedBaggagesGlobal[tabId] || {};
               Object.keys(selectedForTab).forEach(index => {
                  let baggage = selectedForTab[index];
                  if (baggage && baggage.baggageDescription) {
                     $(".infantCountBaggageBtn .BaggageNo").eq(index).text(`(${baggage.baggageDescription})`);
                  }
               });
               let totalPrice = Object.values(selectedForTab).reduce((sum, baggage) => {
                  if (baggage && baggage.baggagePrice) {
                     let price = parseFloat((baggage.baggagePrice || '0').replace(/[^\d.]/g, '')) || 0;
                     return sum + price;
                  }
                  return sum;
               }, 0);

               $(".totalPriceOfBaggage").text("PKR " + totalPrice.toFixed(2));
            }
            let withOutAncis = false;
            let finalPriceTag = totalFare['TotalFare']['@attributes'];
            function checkValidationForAncis() {
               let allValid = true;
               let missing = {
                  seats: [],
                  meals: [],
                  baggage: []
               };

               $.each(selectedSeatsGlobal, function(segmentKey, selectedSeats) {
                  if (!Array.isArray(selectedSeats)) return;
                  if (selectedSeats.length !== passengerList.length) {
                     missing.seats.push(segmentKey);
                     allValid = false;
                  }
               });

               $.each(selectedMealsGlobal, function(segmentKey, paxMeals) {
                  if (Object.keys(paxMeals).length !== passengerList.length) {
                     missing.meals.push(segmentKey);
                     allValid = false;
                  }
               });

               $.each(selectedBaggagesGlobal, function(segmentKey, paxBags) {
                  if (Object.keys(paxBags).length !== passengerList.length) {
                     missing.baggage.push(segmentKey);
                     allValid = false;
                  }
               });

               if (missing.seats.length || missing.meals.length || missing.baggage.length) {
                  let msg = 'Please select the following ancillaries for all passengers:\n';
                  if (missing.seats.length) msg += `\nSeats: ${missing.seats.join(', ')}`;
                  if (missing.meals.length) msg += `\nMeals: ${missing.meals.join(', ')}`;
                  if (missing.baggage.length) msg += `\nBaggage: ${missing.baggage.join(', ')}`;

                  Swal.fire({
                     icon: 'warning',
                     title: 'Missing Selections',
                     text: msg,
                     customClass: {
                        popup: 'text-start'
                     }
                  });
                  return false;
               }
               return true;
            }
            // ------------------------------------ Booking End ------------------------------------ //
            let orderId = null;
            // showOnHoldBooking();
            function bookingAjax(current_fs, next_fs, firstBtn) {
               let user = getUserDetails();
               $.ajax({
                  type: "POST",
                  url: "{{route('bookFlight')}}",
                  data: {
                     // airline: data['airline'], user, paymentOnHold, finalPriceTag, passengers, data, _token: "{{ csrf_token() }}"
                     airline: rawData['airline'],
                     user, passengers,
                     data: rawData,
                     isDirectBooking: isDirectBooking,
                     seats: selectedSeatsGlobal,
                     meals: selectedMealsGlobal,
                     baggages: selectedBaggagesGlobal,
                     _token: "{{ csrf_token() }}"
                  },
                  beforeSend: () => _loader('show'),
                  success: function (response) {
                     $('#paymentSendTest').removeClass('d-none');
                     localStorage.setItem('booking', JSON.stringify(response));
                     showPaymentPage();

                     // Move UI updates here to ensure they run only on success
                     firstBtn = false;
                     let index = $("fieldset").index(next_fs);
                     $("#progressbar li").eq(index).addClass("active");
                     next_fs.show();
                     current_fs.animate({ opacity: 0 }, {
                        step: (now) => {
                           current_fs.css({ 'display': 'none', 'position': 'relative' });
                           next_fs.css({ 'opacity': 1 - now });
                        },
                        duration: 500
                     });
                     setProgressBar(++current);
                  },
                  error: function (xhr) {
                     console.log(xhr.responseJSON || xhr);
                     firstBtn = true;
                     (async () => {
                        let alMsg = xhr.responseJSON.message || 'Please check your details, something seems incorrect.';
                        if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                           let goBack = localStorage.getItem('flights') || null;
                           goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                        }
                     })();
                  },
                  complete: function () {
                     _loader('hide');
                  }
               });
            }
            const getCity = airport => airport?.City || airport?.["@attributes"]?.LocationCode || '--';
         });
         const renderTravelerDetails = booking => {
            const passengers = JSON.parse(booking.passenger_details || "[]");
            const tickets = booking.tickets || [];

            if (!Array.isArray(passengers) || passengers.length === 0) {
               return `<div class="alert alert-danger" role="alert">No passengers found :)</div>`;
            }

            return passengers.map((row, index) => {
               const matchingTicket = tickets.find(
                  t => t.passenger_reference === row.passenger_reference
               );

            const eTicketInfo = (matchingTicket?.ticket_numbers || [])
               .sort((a, b) => Number(a.coupon_no) - Number(b.coupon_no))
               .map(fli => `
                  <div class="col-6">
                     <div class="border rounded p-3 mt-2">
                        <p>Coupon No: <span>${fli.coupon_no}</span></p>
                        <p>Route: <span>${fli.flight_segment}</span></p>
                        <p>ETicket No: <span class="copyText">${fli.e_ticket_no}</span> &nbsp; 
                           <i class="copyBtn fa fa-copy text-black-50" style="cursor:pointer;"></i>
                        </p>
                     </div>
                  </div>
               `).join('');
               return `
                  <div class="custom-method setp-bult traveler-bult row">
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Passenger Details</h1>
                        <p>Traveler ${index + 1}: <span>${paxCapitalize(row.type)}</span></p>
                        <p><span>Name</span>: ${row.given_name}</p>
                        <p><span>Surname</span>: ${row.surname}</p>
                     </div>
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Ticket Details</h1>
                        ${eTicketInfo ? `<div class="row">${eTicketInfo}</div>` : `<div><p>No tickets found</p></div>`}
                     </div>
                  </div>
               `;
            }).join('');
         };
         const renderPaxWithPrice = data => {
            if (data.length === 0) return ``;
            return data.map((row) => `
               <div class="pri-eid">
                  <p>Flyjinnah Airline - (${row.passenger_code})</p>
                  <p>Price: ${row.price_code} ${formatCurrency(row.price)}</p>
               </div>
            `).join('');
         };
         const renderTimeLimitsEmi = data => {
            if (data.length === 0) return ``;
            const payTimeLimit = formatDateTime(data.payment_limit);
            const ticketTimeLimit = formatDateTime(data.ticket_limit);
            return `
               <div class="pri-eid font-weight-bold">
                  <p>Payment Time Limit</p>
                  <p class="font-bold">${payTimeLimit}</p>
               </div>
               <div class="pri-eid font-weight-bold">
                  <p>Ticket Time Limit</p>
                  <p class="font-bold">${ticketTimeLimit}</p>
               </div>
            `;
         };
         const renderTaxDetailsEmi = data => {
            if (!Array.isArray(data) || data.length === 0) return '';
            return data.map(row => {
               if (!row.taxes || !row.price || !row.price_code || !row.passenger_code) {
                  console.warn('Missing required fields in row:', row);
                  return '';
               }
               let taxArray;
               try {
                  taxArray = JSON.parse(row.taxes);
                  if (!Array.isArray(taxArray)) {
                     taxArray = [taxArray];
                  }
               } catch (e) {
                  console.error('Error parsing taxes:', e);
                  return '';
               }

               const totalPrice = parseFloat(row.price) || 0;
               const totalPriceCode = row.price_code;
               const passengers = row.passenger_code;
               const taxSum = taxArray.reduce((sum, tax) => {
                  const amount = parseFloat(tax?.['@attributes']?.Amount) || 0;
                  return sum + amount;
               }, 0);
               const baseAmount = (totalPrice - taxSum).toFixed(2);
               const baseAmountCode = totalPriceCode;

               const taxDetails = taxArray
                  .filter(taxItem => taxItem?.['@attributes']?.TaxName && taxItem?.['@attributes']?.Amount)
                  .map(taxItem => {
                     const tax = taxItem['@attributes'];
                     const description = tax.TaxName;
                     const currency = tax.CurrencyCode || totalPriceCode;
                     return `
                        <div class="pri-eid">
                              <p>${description}</p>
                              <p>Price: ${currency} ${formatCurrency(tax.Amount)}</p>
                        </div>
                     `;
                  })
                  .join('');

               return `
                  <div class="pri-eid font-weight-bold">
                     <h1 class="text-info">Passenger Info: ${passengers}</h1>
                  </div>
                  <div class="pri-eid font-weight-bold">
                     <p>Base Amount</p>
                     <p class="font-bold">Price: ${baseAmountCode} ${formatCurrency(baseAmount)}</p>
                  </div>
                  ${taxDetails}
                  <div class="pri-eid font-weight-bold">
                     <p>Final Amount (${passengers})</p>
                     <p class="font-bold">Price: ${totalPriceCode} ${formatCurrency(totalPrice)}</p>
                  </div>
               `;
            }).join('');
         };
         function setTicketPage (response) {
            let ticketIssued = JSON.parse((localStorage.getItem('ticketIssued') || false));
            let totalPrice = parseInt(response.booking?.price) + parseInt(tax);
            $(".totalPricePaid").text(`Price: ${response.booking?.price_code ?? 'PKR'} ${formatCurrency(totalPrice) ?? 0}`);
            $(".taxPaid").text(`Price: PKR ${formatCurrency(tax)}`);
            $(".guestName").text(response.booking.client.name);
            $(".ticketMsg").text(response.message);
            $(".contactDetails").html(renderTravelerDetails(response.booking));
            $(".paxWithPrice").html(renderPaxWithPrice(response.booking?.booking_items));
            if (!ticketIssued) {
               $(".emiTimeLimitContainer").removeClass('d-none');
               $(".timeLimitsEmi").html(renderTimeLimitsEmi(response.booking));
            }
            $(".taxDetailsEmi").html(renderTaxDetailsEmi(response.booking?.booking_items));
            $(".emiTaxContainer").removeClass('d-none');
            $(".orderId").html(response.booking.order_id);
            // $(".emiServiceContainer").removeClass('d-none');
            // $(".serviceDetailsEmi").html(renderServiceDetailsEmi(response.booking?.booking_items));
         }
         function approveBookingAjax() {
            showOnHoldBooking();
            let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
            if (!createdBooking.booking.id || !createdBooking.booking.client_id) return showMissingDataMsg();
            $.ajax({
               type: "POST",
               url: "{{route('confirm.booking')}}",
               data: {
                  bookingId: createdBooking.booking.id,
                  clientId: createdBooking.booking.client_id,
                  transactionId: createdBooking.booking.transaction_id,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  localStorage.setItem('ticketIssued', JSON.stringify(true));
                  localStorage.setItem('booking', JSON.stringify(response));
                  (async () => {
                     let alMsg = 'Your payment was successful. Ticket details are shown below and will also be sent to your email.';
                     if (await _confirm(alMsg, false, 'success', 'Continue')) {
                        _alert(response.message)
                        setTicketPage(response);
                        showTicketPage();
                        sessionTimer(false);
                     }
                  })();
               },
               error: function (xhr) {
                  (async () => {
                     let phone = "{{ config('variables.contact.phone') }}";
                     let alMsg = `Ticket issue error. please contact us at ${phone} your Order Id is ${orderId ?? 'N/A'}.`;
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        let goBack = localStorage.getItem('flights') || null;
                        goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
      </script>
   @elseif ($isPia)
      <script>
         let data = @json($data);
         let firstBtn = true;
         $(".next").click(async function () {
            let current_fs = $(this).parent();
            let next_fs = current_fs.next();

            if (!validateFields(current_fs)) return;
            try {
               await verifyClient();
               if (firstBtn) {
                  confirmationModal('Please confirm that all the provided details are correct.').then((result) => {
                     if (result.isConfirmed) {
                        $('#paymentSendTest').addClass('d-none');
                        // firstBtn = false;
                        bookingAjax(current_fs, next_fs, firstBtn);
                     } else {
                        _alert('Confirmation cancelled.', 'warning');
                     }
                  });
               } else {
                  let index = $("fieldset").index(next_fs);
                  $("#progressbar li").eq(index).addClass("active");
                  next_fs.show();
                  current_fs.animate({ opacity: 0 }, {
                     step: (now) => {
                        current_fs.css({ 'display': 'none', 'position': 'relative' });
                        next_fs.css({ 'opacity': 1 - now });
                     },
                     duration: 500
                  });
                  setProgressBar(++current);
               }
               // console.log('await');
            } catch (e) {
               // console.log('catch')
               return;
            }
            // console.log('newxt');

         });
         function setTicketPage (response) {
            let totalPrice = parseInt(response.booking?.price) + parseInt(tax);
            $(".totalPricePaid").text(`Price: ${response.booking?.price_code ?? 'PKR'} ${formatCurrency(totalPrice) ?? 0}`);
            $(".taxPaid").text(`Price: PKR ${formatCurrency(tax)}`);
            $(".guestName").text(response.booking.client.name);
            $(".ticketMsg").text(response.message);
            $(".contactDetails").html(renderTravelerDetails(JSON.parse(response.booking?.passenger_details), (response.booking?.tickets || []) ));
            $(".paxWithPrice").html(renderPaxWithPrice(response.booking?.booking_items));
            $(".emiTimeLimitContainer").removeClass('d-none');
            $(".timeLimitsEmi").html(renderTimeLimitsEmi(response.booking));
            // $(".emiTaxContainer").removeClass('d-none');
            // $(".emiServiceContainer").removeClass('d-none');
            $(".serviceDetailsEmi").html(renderServiceDetailsEmi(response.booking?.booking_items));
            $(".orderId").html(response.booking.order_id);
         }
         const renderTravelerDetails = (data, tickets) => {
            if (!Array.isArray(data) || data.length === 0) {
               return `<div class="alert alert-danger" role="alert">Data is missing :)</div>`;
            }
            return data.map((passenger, index) => {
               const matchingTicket = (tickets || []).find(t => t.passenger_reference === passenger.passenger_reference);

               const ticketHtml = matchingTicket ? `
                  <div class="col-6">
                     <div class="border rounded p-3 mt-2">
                        <p>Issue date: <br><span>${formatDateTime(matchingTicket.issue_date)}</span></p>
                        <p>ETicket No: <br><span class="copyText">${matchingTicket.ticket_no}</span></p>
                        <p>Type: <span>E-Ticket</span></p>
                        <p>Price Reference: <span>${matchingTicket.price_reference}</span></p>
                     </div>
                  </div>
               ` : '<div class="col-12">No Ticket Issued</div>';
               return `
                  <div class="custom-method setp-bult traveler-bult row">
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Passenger Details</h1>
                        <p><span>Traveler ${index + 1}</span></p>
                        <p><span>Title</span>: ${passenger.title || ''}</p>
                        <p><span>Name</span>: ${passenger.given_name || passenger.name}</p>
                        <p><span>Surname</span>: ${passenger.surname || passenger.surName}</p>
                     </div>
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Ticket Details</h1>
                        <div class="row">
                           ${ticketHtml}
                        </div>
                     </div>
                  </div>
               `;
            }).join('');
         };
         const renderPaxWithPrice = data => {
            if (data.length === 0) return ``;
            return data.map((row) => `
               <div class="pri-eid">
                  <p>PIA Airline - (${row.passenger_code})</p>
                  <p>Price: ${row.price_code} ${formatCurrency(row.price)}</p>
               </div>
            `).join('');
         };
         const renderTimeLimitsEmi = data => {
            if (data.length === 0) return ``;
            const payTimeLimit = formatDateTime(data.payment_limit);
            const ticketTimeLimit = formatDateTime(data.ticket_limit);
            return `
               <div class="pri-eid font-weight-bold">
                  <p>Payment Time Limit</p>
                  <p class="font-bold">${payTimeLimit}</p>
               </div>
               <div class="pri-eid font-weight-bold">
                  <p>Ticket Time Limit</p>
                  <p class="font-bold">${ticketTimeLimit}</p>
               </div>
            `;
         };
         const renderServiceDetailsEmi = data => {
            if (!data || data.length === 0) return ``;
            return data.map(row => {
               const passengers = row.passenger_code;
               const serviceArray = JSON.parse(row.services || null);
               const serviceDetails = serviceArray.map(service => {
                     if (service.details && service.details.Type && service.details.details?.length > 0) {
                        return `
                           <div class="pri-eid">
                              <p class="font-weight-bold">${service.details.details}</p>
                              <p>${service.details.Type}</p>
                           </div>`;
                     }
                     return '';
               }).join('');
               if (!serviceDetails.trim()) return '';
               return `
                  <div class="pri-eid font-weight-bold">
                     <h5 class="text-info">Passenger Info: ${passengers}</h5>
                  </div>
                  ${serviceDetails}
               `;
            }).join('');
         };
         
         function bookingAjax(current_fs, next_fs, firstBtn) {
            let user = getUserDetails();
            // console.log(passengers, user, data)
            // return
            $.ajax({
               type: "POST",
               url: "{{route('bookFlight')}}",
               data: {
                  user, passengers,
                  airline: data['airline'],
                  data: data['bundle'],
                  paxCount: data['paxCount'],
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  $('#paymentSendTest').removeClass('d-none');
                  localStorage.setItem('booking', JSON.stringify(response));
                  showPaymentPage();

                  // Move UI updates here to ensure they run only on success
                  firstBtn = false;
                  let index = $("fieldset").index(next_fs);
                  $("#progressbar li").eq(index).addClass("active");
                  next_fs.show();
                  current_fs.animate({ opacity: 0 }, {
                     step: (now) => {
                        current_fs.css({ 'display': 'none', 'position': 'relative' });
                        next_fs.css({ 'opacity': 1 - now });
                     },
                     duration: 500
                  });
                  setProgressBar(++current);
               },
               error: function (xhr) {
                  firstBtn = true;
                  console.log(xhr);
                  (async () => {
                     let alMsg = xhr.responseJSON?.message || 'Please check your details, something seems incorrect.';
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        // let goBack = localStorage.getItem('flights') || null;
                        // goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
                  // _alert(xhr.responseJSON.message || 'Booking Error', "error");
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
         function approveBookingAjax() {
            showOnHoldBooking();
            let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
            if (!createdBooking.booking.id || !createdBooking.booking.client_id) return showMissingDataMsg();
            $.ajax({
               type: "POST",
               url: "{{route('confirm.booking')}}",
               data: {
                  bookingId: createdBooking.booking.id,
                  clientId: createdBooking.booking.client_id,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  localStorage.setItem('ticketIssued', JSON.stringify(true));
                  localStorage.setItem('booking', JSON.stringify(response));
                  (async () => {
                     let alMsg = 'Your payment was successful. Ticket details are shown below and will also be sent to your email.';
                     if (await _confirm(alMsg, false, 'success', 'Continue')) {
                        _alert(response.message)
                        setTicketPage(response);
                        showTicketPage();
                        sessionTimer(false);
                     }
                  })();
               },
               error: function (xhr) {
                  (async () => {
                     let phone = "{{ config('variables.contact.phone') }}";
                     let alMsg = `Ticket issue error. please contact us at ${phone} your Order Id is ${orderId ?? 'N/A'}.`;
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        let goBack = localStorage.getItem('flights') || null;
                        goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
      </script>
   @elseif ($isAirblue)
      <script>
         let data = @json($data);
         let firstBtn = true;
         $(".next").click(async function () {
            let current_fs = $(this).parent();
            let next_fs = current_fs.next();

            if (!validateFields(current_fs)) return;
            try {
               await verifyClient();
               if (firstBtn) {
                  confirmationModal('Please confirm that all the provided details are correct.').then((result) => {
                     if (result.isConfirmed) {
                        $('#paymentSendTest').addClass('d-none');
                        // firstBtn = false;
                        bookingAjax(current_fs, next_fs, firstBtn);
                     } else {
                        _alert('Confirmation cancelled.', 'warning');
                     }
                  });
               } else {
                  let index = $("fieldset").index(next_fs);
                  $("#progressbar li").eq(index).addClass("active");
                  next_fs.show();
                  current_fs.animate({ opacity: 0 }, {
                     step: (now) => {
                        current_fs.css({ 'display': 'none', 'position': 'relative' });
                        next_fs.css({ 'opacity': 1 - now });
                     },
                     duration: 500
                  });
                  setProgressBar(++current);
               }
               // console.log('await');
            } catch (e) {
               // console.log('catch')
               return;
            }
            // console.log('newxt');

         });
         // ===== GLOBAL STATE =====
         let seatState = {
            bookingTag: null,
            passengers: [],
            flights: {},        // rph => flight
            selections: {}      // flightRph => paxRph => seat
         };
         const ancisState = {
            bookingTag: null,
            passengers: [],
            flights: {},        // rph => flight data
            selections: {}      // flightRph => paxRph => groupCode => [items]
         };
         // Remove THIS
         let storageUserData = JSON.parse(localStorage.getItem('userData')) || {};
         if (storageUserData && Array.isArray(storageUserData.passengers) && storageUserData.user) {
            setAndLockForm(storageUserData.passengers, storageUserData.user);
         }
         let dynamicBooking = localStorage.getItem('booking');
         let bookingResponse = localStorage.getItem('bookingResponseAirblue');
         let seatConfirmed = localStorage.getItem('seatConfirmed');
         let ancillariesConfirmed = localStorage.getItem('ancillariesConfirmed');
         let ticketIssued = JSON.parse((localStorage.getItem('ticketIssued') || false));
         if (dynamicBooking) {
            dynamicBooking = JSON.parse(dynamicBooking);
            showPaymentPage();
            $('#paymentSendTest').removeClass('d-none');
         }
         if (bookingResponse) {
            bookingResponse = JSON.parse(bookingResponse);
            showAncisSelection(bookingResponse);
            // $('#paymentSendTest').removeClass('d-none');
         }
         if (bookingResponse && seatConfirmed && ancillariesConfirmed && !ticketIssued && !dynamicBooking) {
            seatConfirmed = JSON.parse(seatConfirmed);
            ancillariesConfirmed = JSON.parse(ancillariesConfirmed);
            createBooking(ancillariesConfirmed);
            // showPaymentPage();
         }
         function bookingAjax(current_fs, next_fs, firstBtn) {
            let user = getUserDetails();
            // console.log('bookingAjax run');
            // return;
            $.ajax({
               type: "POST",
               url: "{{route('bookFlight')}}",
               data: {
                  user, passengers,
                  status: 'fetch',
                  airline: data['airline'],
                  data, paxCount: data['paxCount'],
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  // console.log(response)
                  localStorage.setItem('bookingResponseAirblue', JSON.stringify(response));
                  localStorage.setItem('userData', JSON.stringify({user, passengers}));
                  showAncisSelection(response);

                  // showPaymentPage();
                  // firstBtn = false;
                  // let index = $("fieldset").index(next_fs);
                  // $("#progressbar li").eq(index).addClass("active");
                  // next_fs.show();
                  // current_fs.animate({ opacity: 0 }, {
                  //    step: (now) => {
                  //       current_fs.css({ 'display': 'none', 'position': 'relative' });
                  //       next_fs.css({ 'opacity': 1 - now });
                  //    },
                  //    duration: 500
                  // });
                  // setProgressBar(++current);
               },
               error: function (xhr) {
                  firstBtn = true;
                  console.log(xhr);
                  (async () => {
                     let alMsg = xhr.responseJSON?.message || 'Please check your details, something seems incorrect.';
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        // let goBack = localStorage.getItem('flights') || null;
                        // goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
                  // _alert(xhr.responseJSON.message || 'Booking Error', "error");
               },
               complete: function () {
                  _loader('hide');
               }
            });
         };
         function showAncisSelection(bookingResponse) {
            if (!bookingResponse || !bookingResponse.booking_refs) return;
            $('#contactSubmit').hide();
            setFormDisabled(true);
            seatState.bookingTag = bookingResponse.booking_refs[1] || bookingResponse.booking_refs[0] || null;
            
            // Filter passengers (keep your existing filter if needed)
            seatState.passengers = bookingResponse.passengers.filter(p => 
               p.type !== 'INF'  // adjust if needed
            );
            
            seatState.flights = {};
            seatState.selections = {};
            
            // IMPORTANT: Use the segment RPH (like "1", "2") as key, NOT the leg key like "B1"
            bookingResponse.legs.forEach(leg => {
               leg.segments.forEach(segment => {
                     // segment.rph is usually "1", "2", etc. for outbound/return
                     seatState.flights[segment.rph] = {
                        ...segment,
                        legRph: leg.rph  // optional: keep reference if needed elsewhere
                     };
               });
            });
            if (!seatConfirmed) {
               getSeatAjax(bookingResponse);
            } 
            if(seatConfirmed && !ancillariesConfirmed) {
               getAncillaryAjax({bookingTag: seatState.bookingTag,
                  legs: Object.values(seatState.flights).map(f => ({
                     rph: f.legRph,
                     segments: [f]
                  }))
               });
            }
         }
         function getSeatAjax(bookingResponse) {
            $.ajax({
               type: "POST",
               url: "{{ route('get_seat') }}",
               data: {
                  data: {
                     bookingTag: seatState.bookingTag,
                     legs: bookingResponse.legs
                  },
                  airline: data.airline,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: res => {
                  buildSeatUI(res)
               },
               complete: () => _loader('hide')
            });
         }
         function buildSeatUI(res) {
            const flights = Object.values(res.flights ?? res)
               .filter(f => f && f.rph && f.rows);
            
            const $c = $('.addOnsContainer').empty();
            
            // Passenger buttons
            let paxBtns = seatState.passengers.map((p, i) => `
               <button type="button" class="btn btn-outline-info pax-btn ${i === 0 ? 'active' : ''}" 
                        data-rph="${p.rph}" data-name="${p.name.first} ${p.name.last}">
                     ${p.name.first} ${p.name.last}
               </button>
            `).join('');
            
            // Flight tabs
            let flightTabs = '';
            let flightBodies = '';
            
            flights.forEach((f, i) => {
               const segment = `${f.departure_airport}-${f.arrival_airport}`;
               flightTabs += `
                     <li class="nav-item">
                        <a class="nav-link ${i === 0 ? 'active' : ''}" 
                           data-toggle="tab" 
                           href="#flight-${f.rph}">
                           ${segment}
                        </a>
                     </li>`;
               
               flightBodies += `
                  <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="flight-${f.rph}">
                     <div class="plane">
                        <div class="winds1">
                           <div class="winds2">
                              <div class="windows">
                                 <div class="windows2">
                                    <div class="seatMap d-flex" data-rph="${f.rph}" data-segment="${segment}"></div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>`;
            });
            
            // <button id="fetchAgainSeats" class="btn btn-success">Fetch Again</button>
            $c.html(`
               <h2 class="my-3 text-info font-weight-bolder">Seat Selection</h2>
               <div class="mb-3">${paxBtns}</div>
               <ul class="d-flex justify-content-start nav nav-tabs">${flightTabs}</ul>
               <div class="tab-content mt-3">${flightBodies}</div>
               <div class="d-flex justify-content-between mt-3">
                  <strong>Total: <span class="totalPriceOfSeat">PKR 0</span></strong>
                  <button type="button" id="confirmSeatsBtn" class="btn btn-outline-info">Confirm Seats</button>
               </div>
            `);
            
            bindSeatEvents();
            flights.forEach(f => renderSeatMap(f));
         }
         function renderSeatMap(flight) {
            if (!flight || !flight.rows) return;

            const $map = $(`.seatMap[data-rph="${flight.rph}"]`).empty();

            Object.values(flight.rows).forEach(row => {
               const $row = $('<div class="seat-row">');

               row.seats.forEach(s => {
                  if (s.type === 'gap') {
                     $row.append('<div class="aisle"></div>');
                     return;
                  }

                  const seatId = row.row_number + s.seat_number;
                  const price = s.price || 0;

                  const $seat = $('<div class="seat">')
                     .text(seatId)
                     .toggleClass('occupied', s.occupied || !s.available)
                     .attr(
                        'title',
                        price > 0
                           ? `Seat ${seatId} - PKR ${price}`
                           : `Seat ${seatId} - Free`
                     )
                     .data({
                        flightRph: flight.rph,
                        flight_number: flight.flight_number,
                        row: row.row_number,
                        seat: s.seat_number,
                        price,
                        segment: $map.data('segment')
                     });

                  $row.append($seat);
               });

               $map.append($row);
            });
         }
         function bindSeatEvents() {
            // Passenger switch
            $(document).off('click', '.pax-btn').on('click', '.pax-btn', function() {
               $('.pax-btn').removeClass('active');
               $(this).addClass('active');
               refreshAllSeatHighlights(); // update visuals when switching passenger
            });
            
            // Seat click - toggle select/deselect
            $(document).off('click', '.seat').on('click', '.seat', function() {
               if ($(this).hasClass('occupied')) return;
               
               const paxRph = $('.pax-btn.active').data('rph');
               if (!paxRph) {
                     _alert('Please select a passenger first.', 'warning');
                     return;
               }
               
               const d = $(this).data();
               
               // Initialize nested objects if needed
               seatState.selections[d.flightRph] ||= {};
               
               // Toggle selection
               if (seatState.selections[d.flightRph][paxRph]) {
                     // Deselect
                     delete seatState.selections[d.flightRph][paxRph];
                     if (Object.keys(seatState.selections[d.flightRph]).length === 0) {
                        delete seatState.selections[d.flightRph];
                     }
               } else {
                     // Select new seat
                     seatState.selections[d.flightRph][paxRph] = {
                        flight_number: d.flight_number,
                        seat_number: d.seat,
                        row_number: d.row,
                        traveler_no: paxRph,
                        rph: d.flightRph,
                        price: d.price
                     };
               }
               
               refreshAllSeatHighlights();
               updateSeatTotal();
            });
         }
         function refreshAllSeatHighlights() {
            $('.seat').removeClass('selected');
            
            Object.keys(seatState.selections).forEach(flightRph => {
               Object.keys(seatState.selections[flightRph]).forEach(paxRph => {
                     const seatInfo = seatState.selections[flightRph][paxRph];
                     const seatId = seatInfo.row_number + seatInfo.seat_number;
                     $(`.seatMap[data-rph="${flightRph}"] .seat`)
                        .filter(function() { return $(this).text() === seatId; })
                        .addClass('selected');
               });
            });
         }
         function updateSeatTotal() {
            let total = 0;
            Object.values(seatState.selections).forEach(flight => {
               Object.values(flight).forEach(seat => {
                     total += seat.price || 0;
               });
            });
            $('.totalPriceOfSeat').text('PKR ' + total);
         }
         $(document).on('click', '#confirmSeatsBtn', async function() {
            const missing = [];
            
            console.log('Current seatState:', seatState);
            
            // Validation: Check for missing seats
            seatState.passengers.forEach(pax => {
               Object.values(seatState.flights).forEach(flight => {
                     if (!seatState.selections[flight.rph]?.[pax.rph]) {
                        const dep = flight.departure?.airport || flight.departure_airport || '???';
                        const arr = flight.arrival?.airport || flight.arrival_airport || '???';
                        const segment = `${dep}-${arr}`;
                        
                        const paxName = $('.pax-btn[data-rph="' + pax.rph + '"]').data('name') 
                                       || `${pax.name.first} ${pax.name.last}`;
                        
                        missing.push({
                           passenger: paxName,
                           segment: segment
                        });
                     }
               });
            });
            
            if (missing.length > 0) {
               const messages = missing.map(m => 
                     `• ${m.passenger} on segment ${m.segment}`
               ).join('<br>');
               
               (async () => {
                  let alMsg = `Please select seats for the following:<br><br>${messages}`;
                  if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                     // let goBack = localStorage.getItem('flights') || null;
                     // goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                  }
               })();
               // _alert(`Please select seats for the following:<br><br>${messages}`, 'warning');
               return;
            }
            
            if (!(await _confirm('Confirm selected seats?', true))) return;
            
            // === GROUPED PAYLOAD BY FLIGHT ===
            const groupedPayload = {};
            
            Object.keys(seatState.selections).forEach(flightRph => {
               const flightSelections = seatState.selections[flightRph];
               const flightInfo = seatState.flights[flightRph];
               
               // Build a clean flight identifier (you can adjust fields as needed)
               const dep = flightInfo.departure?.airport || flightInfo.departure_airport;
               const arr = flightInfo.arrival?.airport || flightInfo.arrival_airport;
               const flightNumber = Object.values(flightSelections)[0]?.flight_number || '';
               
               groupedPayload[flightRph] = {
                     rph: flightRph,
                     flight_number: flightNumber,
                     departure_airport: dep,
                     arrival_airport: arr,
                     seats: Object.values(flightSelections).map(seat => ({
                        traveler_no: seat.traveler_no,
                        seat_number: seat.seat_number,
                        row_number: seat.row_number,
                        price: seat.price || 0
                     }))
               };
            });
            
            // Final structured payload
            const finalPayload = {
               bookingTag: seatState.bookingTag,
               seats: Object.values(groupedPayload)  // array of flights with their seats
            };
            submitSeatSelection(finalPayload);
            console.log('FINAL GROUPED SEAT PAYLOAD:', finalPayload);
            
            
            // TODO: Send this to your backend
            
            // proceedToNextStep();
         });
         function submitSeatSelection(payload) {
            $.ajax({
               type: 'POST',
               url: "{{ route('confirm_seats') }}",
               data: {
                  data: payload,
                  airline: data.airline,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: (response) => {
                  _alert('Seats confirmed successfully!', 'success');
                  localStorage.setItem('seatConfirmed', JSON.stringify(response));
                  getAncillaryAjax({bookingTag: seatState.bookingTag,
                     legs: Object.values(seatState.flights).map(f => ({
                        rph: f.legRph,
                        segments: [f]
                     }))
                  });
               },
               error: function (xhr) {
                  // console.log('submitSeatSelection', xhr);
                  (async () => {
                     let alMsg = xhr.responseJSON?.message || 'Seats unavailable. Reload seats?';
                     if (await _confirm(alMsg, false, 'warning', 'Reload seats')) {
                           getSeatAjax({
                              bookingTag: seatState.bookingTag,
                              legs: Object.values(seatState.flights).map(f => ({
                                 rph: f.legRph,
                                 segments: [f]
                              }))
                           });
                     }
                  })();
               },
               complete: () => _loader('hide')
            });
         }
         // Ancillary
         function getAncillaryAjax(bookingResponse) {
            $.ajax({
               type: "POST",
               url: "{{ route('get_baggage') }}",
               data: {
                  data: {
                     bookingTag: seatState.bookingTag,
                     legs: bookingResponse.legs
                  },
                  airline: data.airline,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: res => buildAncillaryUI(res),
               complete: () => _loader('hide')
            });
         }
         function buildAncillaryUI(res) {
            const flights = res.flights || [];

            ancisState.bookingTag = seatState.bookingTag;
            ancisState.passengers = seatState.passengers.filter(p => p.type !== 'INF');
            ancisState.flights = {};
            ancisState.selections = {};  // Clear previous selections

            const seatStateFlights = Object.values(seatState.flights || {});
            // console.log('seatStateFlights',seatStateFlights);
            const seatFlightMap = {};
            seatStateFlights.forEach(f => {
               const key = `${f.flight_number}-${f.departure.airport}-${f.arrival.airport}`;
               seatFlightMap[key] = f.rph; // 👈 THIS is the RPH you want
            });
            flights.forEach(f => {
               const key = `${f.flight_number}-${f.from}-${f.to}`;
               const seatRph = seatFlightMap[key];

               if (!seatRph) {
                  console.warn('No matching seat flight for ancillary flight', f);
                  return;
               }

               f.seatRph = seatRph;

               ancisState.flights[seatRph] = f;
               // ancisState.flights[f.rph] = f;
            });

            const $c = $('.addOnsContainer').empty();

            const paxBtns = ancisState.passengers.map((p, i) => `
               <button type="button" class="btn btn-outline-info pax-btn ${i === 0 ? 'active' : ''}"
                  data-rph="${p.rph}" data-name="${p.name.first} ${p.name.last}">
                  ${p.name.first} ${p.name.last}
               </button>
            `).join('');

            let tabs = '';
            let bodies = '';

            flights.forEach((f, i) => {
               const segment = `${f.from}-${f.to}`;
               tabs += `
                  <li class="nav-item">
                     <a class="nav-link ${i === 0 ? 'active' : ''}" data-toggle="tab" href="#ancis-${f.seatRph}">
                        ${segment}
                     </a>
                  </li>`;

               bodies += `
                  <div class="tab-pane fade ${i === 0 ? 'show active' : ''}" id="ancis-${f.seatRph}">
                     ${renderAncillaryGroups(f)}
                  </div>`;
            });
            // <button id="fetchAgainAncillaries" class="btn btn-success mb-3">Fetch Again</button>
            $c.html(`
               <h2 class="my-3 text-info font-weight-bolder">Add-ons Selection</h2>
               <div class="mb-3">${paxBtns}</div>
               <ul class="d-flex justify-content-start nav nav-tabs">${tabs}</ul>
               <div class="tab-content mt-3">${bodies}</div>
               <div class="d-flex justify-content-between mt-4">
                  <strong>Total: <span class="totalAncillaryPrice">PKR 0</span></strong>
                  <button type="button" id="confirmAncillariesBtn" class="btn btn-outline-info">Confirm Add-ons</button>
               </div>
            `);

            initializeDefaultUI();
            bindAncillaryEvents();
            refreshAncillaryHighlights();
            updateAncillaryTotal();
         }
         function renderAncillaryGroups(flight) {
            return flight.ancillaries.map(group => {
               const isBaggage = group.group_code === 'XBAG';
               const isWheelchair = group.group_code === 'WCHR';

               return `
                  <div class="mb-5 p-3 border rounded ${isBaggage ? 'border-danger bg-light' : ''}">
                     <h5 class="font-weight-bold mb-2">
                        ${group.title} 
                        ${isBaggage ? '<span class="text-danger">*</span> <small class="text-danger">(Required)</small>' : ''}
                     </h5>
                     <p class="text-muted mb-3">${group.description || ''}</p>
                     <div class="d-flex flex-wrap gap-3">
                        ${group.items.map(item => {
                           const priceText = item.available && item.price > 0
                              ? `– ${item.currency} ${item.price}`
                              : item.available ? '– Free' : '(Unavailable)';

                           let imgSrc = '/assets/images/baggagedefault.jpg';
                           if (isWheelchair) imgSrc = '/assets/images/wheelchair.png';

                           return `
                              <div class="card ancillary-card mr-3 ${!item.available ? 'text-muted disabled' : ''}"
                                 style="width: 160px; cursor: pointer;"
                                 data-flight="${flight.seatRph}"
                                 data-group="${group.group_code}"
                                 data-code="${item.code}"
                                 data-price="${item.price || 0}">
                                 <img src="${imgSrc}" class="card-img-top" alt="${item.title}">
                                 <div class="card-body p-2 text-center">
                                    <h6 class="card-title mb-1">${item.title}</h6>
                                    <p class="card-text mb-0">${priceText}</p>
                                    <input type="${group.multiple ? 'checkbox' : 'radio'}"
                                          class="d-none ancillary-item"
                                          name="ancis-${flight.rph}-${group.group_code}"
                                          ${!item.available ? 'disabled' : ''}
                                          data-flight="${flight.rph}"
                                          data-group="${group.group_code}"
                                          data-code="${item.code}">
                                 </div>
                              </div>
                           `;
                        }).join('')}

                        <!-- Visual "No Wheelchair" Default Option -->
                        ${isWheelchair ? `
                           <div class="card ancillary-card mr-3 selected"
                              style="width: 160px; cursor: pointer;"
                              data-flight="${flight.rph}"
                              data-group="${group.group_code}"
                              data-code="no-wheelchair"
                              data-price="0">
                              <img src="/assets/images/wheelchair.png" class="card-img-top" alt="No Wheelchair">
                              <div class="card-body p-2 text-center">
                                 <h6 class="card-title mb-1">No Wheelchair</h6>
                                 <p class="card-text mb-0">– Free</p>
                                 <input type="radio" class="d-none ancillary-item"
                                       name="ancis-${flight.rph}-${group.group_code}"
                                       checked
                                       data-flight="${flight.rph}"
                                       data-group="${group.group_code}"
                                       data-code="no-wheelchair">
                              </div>
                           </div>
                        ` : ''}
                     </div>
                  </div>
               `;
            }).join('');
         }
         function initializeDefaultUI() {
            $('.ancillary-card[data-code="no-wheelchair"]').addClass('selected')
               .find('.ancillary-item').prop('checked', true);
         }
         function bindAncillaryEvents() {
            $(document).off('click', '.pax-btn').on('click', '.pax-btn', function () {
               $('.pax-btn').removeClass('active');
               $(this).addClass('active');
               refreshAncillaryHighlights();
               updateAncillaryTotal();
            });

            $(document).off('click', '.ancillary-card').on('click', '.ancillary-card', function () {
               const $card = $(this);
               const $input = $card.find('.ancillary-item');
               if ($input.is(':disabled')) return;

               const groupCode = $card.data('group');
               const isRadio = $input.attr('type') === 'radio';
               const isNoWheelchair = $card.data('code') === 'no-wheelchair';

               if (isRadio) {
                  $card.siblings('.ancillary-card').removeClass('selected')
                     .find('.ancillary-item').prop('checked', false);
               }

               const shouldCheck = !$input.prop('checked');
               $input.prop('checked', shouldCheck);
               $card.toggleClass('selected', shouldCheck);

               // If user picks "No Wheelchair", clear any real wheelchair selection
               if (isNoWheelchair && shouldCheck) {
                  const paxRph = $('.pax-btn.active').data('rph');
                  const flightRph = $card.data('flight');
                  if (ancisState.selections[flightRph]?.[paxRph]?.[groupCode]) {
                     delete ancisState.selections[flightRph][paxRph][groupCode];
                  }
               }

               $input.trigger('change');
            });

            $(document).off('change', '.ancillary-item').on('change', '.ancillary-item', function () {
               const paxRph = $('.pax-btn.active').data('rph');
               if (!paxRph) return;

               const $input = $(this);
               const d = $input.closest('.ancillary-card').data();
               const isNoWheelchair = d.code === 'no-wheelchair';

               // NEVER save "no-wheelchair" to state
               if (isNoWheelchair) {
                  updateAncillaryTotal();
                  return;
               }

               // Normal ancillary item
               ancisState.selections[d.flight] ||= {};
               ancisState.selections[d.flight][paxRph] ||= {};

               const groupKey = d.group;
               const isMultiple = $input.attr('type') === 'checkbox';

               if (!isMultiple) {
                  // Radio (single selection) → replace entire group
                  ancisState.selections[d.flight][paxRph][groupKey] = [];
               } else {
                  // Checkbox → ensure array exists
                  ancisState.selections[d.flight][paxRph][groupKey] ||= [];
               }

               if (this.checked) {
                  ancisState.selections[d.flight][paxRph][groupKey].push({
                     code: d.code,
                     price: parseFloat(d.price) || 0
                  });
               } else {
                  ancisState.selections[d.flight][paxRph][groupKey] =
                     ancisState.selections[d.flight][paxRph][groupKey].filter(i => i.code !== d.code);
               }

               updateAncillaryTotal();
            });
         }
         function refreshAncillaryHighlights() {
            const paxRph = $('.pax-btn.active').data('rph');
            if (!paxRph) return;

            // Reset all visuals
            $('.ancillary-card').removeClass('selected');
            $('.ancillary-item').prop('checked', false);

            // Apply real selections
            Object.keys(ancisState.selections).forEach(flightRph => {
               const paxData = ancisState.selections[flightRph]?.[paxRph];
               if (paxData) {
                  Object.keys(paxData).forEach(groupCode => {
                     paxData[groupCode].forEach(item => {
                        const selector = `.ancillary-card[data-flight="${flightRph}"][data-group="${groupCode}"][data-code="${item.code}"]`;
                        $(selector).addClass('selected')
                           .find('.ancillary-item').prop('checked', true);
                     });
                  });
               } else {
                  // No real selection → show "No Wheelchair" as default (visual only)
                  $(`.ancillary-card[data-flight="${flightRph}"][data-code="no-wheelchair"]`)
                     .addClass('selected')
                     .find('.ancillary-item').prop('checked', true);
               }
            });
         }
         function updateAncillaryTotal() {
            let total = 0;
            Object.values(ancisState.selections).forEach(flight =>
               Object.values(flight).forEach(pax =>
                  Object.values(pax).forEach(group =>
                     group.forEach(item => total += item.price)
                  )
               )
            );
            $('.totalAncillaryPrice').text('PKR ' + total.toFixed(0));
         }
         // CORRECTED BAGGAGE VALIDATION
         function validateBaggageSelection() {
            const missing = [];

            ancisState.passengers.forEach(pax => {
               Object.values(ancisState.flights).forEach(flight => {
                  const baggageGroup = flight.ancillaries.find(g => g.group_code === 'XBAG');
                  if (!baggageGroup) return; // No baggage on this flight

                  const selections = ancisState.selections[flight.seatRph]?.[pax.rph]?.['XBAG'];
                  const hasBaggage = selections && selections.length > 0;

                  if (!hasBaggage) {
                     const paxName = $('.pax-btn[data-rph="' + pax.rph + '"]').data('name') ||
                                    `${pax.name.first} ${pax.name.last}`;
                     const segment = `${flight.from}-${flight.to}`;
                     missing.push(`${paxName} → ${segment}`);
                  }
               });
            });

            return missing;
         }
         $(document).on('click', '#confirmAncillariesBtn', async function () {
            const missingBaggage = validateBaggageSelection();

            if (missingBaggage.length > 0) {
               const list = missingBaggage.map(m => `• ${m}`).join('<br>');
               
               (async () => {
                  let alMsg = `Baggage selection is required for:<br>${list}`;
                  await _confirm(alMsg, false, 'warning', 'Go Back');
                  return;
               })();
               return;
            }

            if (!(await _confirm('Confirm all selected add-ons?', true))) return;

            const payload = {
               bookingTag: ancisState.bookingTag,
               ancillaries: []
            };

            // Only include REAL selections (never "no-wheelchair")
            Object.keys(ancisState.selections).forEach(flightRph => {
               Object.keys(ancisState.selections[flightRph]).forEach(paxRph => {
                  Object.keys(ancisState.selections[flightRph][paxRph]).forEach(groupCode => {
                     ancisState.selections[flightRph][paxRph][groupCode].forEach(item => {
                        if (item.code === 'no-wheelchair') return; // Skip fake
                        payload.ancillaries.push({
                           rph: flightRph,
                           traveler_no: paxRph,
                           group_code: groupCode,
                           code: item.code,
                           price: item.price
                        });
                     });
                  });
               });
            });

            console.log('FINAL ANCILLARY PAYLOAD:', payload);
            submitAncillaries(payload);
         });
         function submitAncillaries(payload) {
            // console.log('submitAncis', payload)
            // return;
            $.ajax({
               type: 'POST',
               url: "{{ route('confirm_ancillaries') }}",
               data: {
                  data: payload,
                  airline: data.airline,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: (response) => {
                  localStorage.setItem('ancillariesConfirmed', JSON.stringify(response));
                  _alert('Add-ons confirmed successfully!', 'success');
                  createBooking(response);
               },
               error: function (xhr) {
                  (async () => {
                     const msg = xhr.responseJSON?.message || 'Failed to confirm add-ons. Reload?';
                     if (await _confirm(msg, false, 'warning', 'Reload')) {
                        getAncillaryAjax({
                           bookingTag: seatState.bookingTag,
                           legs: Object.values(seatState.flights).map(f => ({
                              rph: f.legRph,
                              segments: [f]
                           }))
                        });
                     }
                  })();
               },
               complete: () => _loader('hide')
            });
         }
         $(document).on('click', '#fetchAgainSeats', function() {
            // let baggage = localStorage.getItem('baggage');
            // console.log('baggage', JSON.parse(baggage));
            // if(baggage){
            //    return buildAncillaryUI(JSON.parse(baggage));
            // }
            getAncillaryAjax({
               bookingTag: seatState.bookingTag,
               legs: Object.values(seatState.flights).map(f => ({
                  rph: f.legRph,
                  segments: [f]
               }))
            });
            // proceedToNextStep();
         });
         function createBooking(response) {
            let user = getUserDetails();
            if (!Array.isArray(passengers) || passengers.length === 0) {
               const rawPassengers = localStorage.getItem('userData');
               if (rawPassengers) {
                  passengers = JSON.parse(rawPassengers).passengers;
               }
            }
            $.ajax({
               type: "POST",
               url: "{{ route('bookFlight') }}",
               data: {
                  user, passengers,
                  status: 'create',
                  airline: data['airline'],
                  data: response,
                  paxCount: data['paxCount'],
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: bookingResponse => {
                  localStorage.setItem('booking', JSON.stringify(bookingResponse));
                  showPaymentPage();
                  $('#paymentSendTest').removeClass('d-none');
                  
               },
               error: function (xhr) {
                  (async () => {
                     let alMsg = xhr.responseJSON?.message || 'Booking failed. Retry?';
                     if (await _confirm(alMsg, false, 'warning', 'Retry')) {
                        createBooking(response);
                     }
                  })();
               },
               complete: () => _loader('hide')
            });
         }

         function setTicketPage (response) {
            let totalPrice = parseInt(response.booking?.price) + parseInt(tax);
            $(".totalPricePaid").text(`Price: ${response.booking?.price_code ?? 'PKR'} ${formatCurrency(totalPrice) ?? 0}`);
            $(".taxPaid").text(`Price: PKR ${formatCurrency(tax)}`);
            $(".guestName").text(response.booking.client.name);
            $(".ticketMsg").text(response.message);
            $(".contactDetails").html(renderTravelerDetails(JSON.parse(response.booking?.passenger_details), (response.booking?.tickets || []) ));
            $(".paxWithPrice").html(renderPaxWithPrice(response.booking?.booking_items));
            $(".emiTimeLimitContainer").removeClass('d-none');
            $(".timeLimitsEmi").html(renderTimeLimitsEmi(response.booking));
            // $(".emiTaxContainer").removeClass('d-none');
            $(".serviceDetailsEmi").html(renderServiceDetailsEmi(response.booking?.booking_items, JSON.parse(response.booking?.passenger_details)));
            $(".orderId").html(response.booking.order_id);
         }
         const renderTravelerDetails = (data, tickets) => {
            if (!Array.isArray(data) || data.length === 0) {
               return `<div class="alert alert-danger" role="alert">Data is missing :)</div>`;
            }
            return data.map((passenger, index) => {
               const matchingTicket = (tickets || []).find(t => t.passenger_reference === passenger.passenger_reference);

               const ticketHtml = matchingTicket ? `
                  <div class="col-6">
                     <div class="border rounded p-3 mt-2">
                        <p>Issue date: <br><span>${formatDateTime(matchingTicket.issue_date)}</span></p>
                        <p>ETicket No: <br><span class="copyText">${matchingTicket.ticket_no}</span></p>
                        <p>Type: <span>E-Ticket</span></p>
                     </div>
                  </div>
               ` : '<div class="col-12">No Ticket Issued</div>';
               return `
                  <div class="custom-method setp-bult traveler-bult row">
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Passenger Details</h1>
                        <p><span>Traveler ${index + 1}</span></p>
                        <p><span>Title</span>: ${passenger.title || ''}</p>
                        <p><span>Name</span>: ${passenger.given_name || passenger.name}</p>
                        <p><span>Surname</span>: ${passenger.surname || passenger.surName}</p>
                     </div>
                     <div class="col-md-6 col-12">
                        <h1 class="font-weight-bold mb-3">Ticket Details</h1>
                        <div class="row">
                           ${ticketHtml}
                        </div>
                     </div>
                  </div>
               `;
            }).join('');
         };
         const renderPaxWithPrice = data => {
            if (data.length === 0) return ``;
            return data.map((row) => `
               <div class="pri-eid">
                  <p>AIRBLUE Airline - (${row.passenger_code})</p>
                  <p>Price: ${row.price_code} ${formatCurrency(row.price)}</p>
               </div>
            `).join('');
         };
         const renderTimeLimitsEmi = data => {
            if (data.length === 0) return ``;
            const payTimeLimit = formatDateTime(data.payment_limit);
            const ticketTimeLimit = formatDateTime(data.ticket_limit);
            return `
               <div class="pri-eid font-weight-bold">
                  <p>Payment Time Limit</p>
                  <p class="font-bold">${payTimeLimit}</p>
               </div>
               <div class="pri-eid font-weight-bold">
                  <p>Ticket Time Limit</p>
                  <p class="font-bold">${ticketTimeLimit}</p>
               </div>
            `;
         };
         const renderServiceDetailsEmi = (data, passengers) => {
            console.log(data, passengers)
            $(".emiServiceContainer").removeClass('d-none');
            if (!data || data.length === 0) return ``;

            return data.map(row => {
               const passenger = passengers.find(p => p.passenger_reference === row.passenger_ref);
               const passengerType = row.passenger_code || 'Passenger';
               const services = JSON.parse(row.services || '[]');
               if (services.length === 0) return '';

               return services.map(service => {
                  const title = service.title || service.itemTitle || 'Service';
                  const price = service.price
                     ? `${service.currency || 'PKR'} ${formatCurrency(service.price)}`
                     : 'Free';
                  const status = service.status || 'Held';

                  return `
                     <div class="pri-eid flex-column align-items-start">
                        <h6 class="font-weight-bold text-info mb-3"> <i class="fas fa-user"></i> Passenger: ${passenger.given_name} ${passenger.surname} (${passengerType}) </h6>
                        <p>
                           ${title} — Status: ${status}
                        </p>
                        <p>${price}</p>
                     </div>
                  `;
               }).join('');
            }).join('');
         };
         function approveBookingAjax() {
            showOnHoldBooking();
            let createdBooking = JSON.parse((localStorage.getItem('booking') || null));
            if (!createdBooking.booking.id || !createdBooking.booking.client_id) return showMissingDataMsg();
            $.ajax({
               type: "POST",
               url: "{{route('confirm.booking')}}",
               data: {
                  bookingId: createdBooking.booking.id,
                  clientId: createdBooking.booking.client_id,
                  _token: "{{ csrf_token() }}"
               },
               beforeSend: () => _loader('show'),
               success: function (response) {
                  localStorage.setItem('ticketIssued', JSON.stringify(true));
                  localStorage.setItem('booking', JSON.stringify(response));
                  (async () => {
                     let alMsg = 'Your payment was successful. Ticket details are shown below and will also be sent to your email.';
                     if (await _confirm(alMsg, false, 'success', 'Continue')) {
                        _alert(response.message)
                        setTicketPage(response);
                        showTicketPage();
                        sessionTimer(false);
                     }
                  })();
               },
               error: function (xhr) {
                  (async () => {
                     let phone = "{{ config('variables.contact.phone') }}";
                     let alMsg = `Ticket issue error. please contact us at ${phone} your Order Id is ${orderId ?? 'N/A'}.`;
                     if (await _confirm(alMsg, false, 'warning', 'Go Back')) {
                        let goBack = localStorage.getItem('flights') || null;
                        goBack ? window.location.href = `/flights${goBack}` : window.history.back();
                     }
                  })();
               },
               complete: function () {
                  _loader('hide');
               }
            });
         }
      </script>
   @endif
@endif
@endsection