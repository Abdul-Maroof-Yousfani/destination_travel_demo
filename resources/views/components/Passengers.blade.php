@php
   $user = auth()->guard('client')->user();
   if ($user) $user->load('passengers');
   // dd($user);
@endphp
<div class="contact">
   <h2>Contact Details</h2>
   <div class="row">
      <div class="col-lg-6">
         <div class="form-group">
            <label for="gendertitle">Select Title</label>
            <div class="inline-flex">
               <label for="title_mr_user" class="borlook d-flex p-3">
                  <input class="hidden-radio" type="radio" id="title_mr_user" value="MR" name="user_title" {{ $user?->title == 'MR' ? 'checked' : '' }} {{ !$user?->title ? 'checked' : '' }} />
                  <span class="ml-2">Mr</span>
               </label>
               <label for="title_mrs_user" class="borlook d-flex p-3">
                  <input class="hidden-radio" type="radio" id="title_mrs_user" value="MRS" name="user_title" {{ $user?->title == 'MRS' ? 'checked' : '' }} />
                  <span class="ml-2">Mrs</span>
               </label>
               <label for="title_ms_user" class="borlook d-flex p-3">
                  <input class="hidden-radio" type="radio" id="title_ms_user" value="MS" name="user_title" {{ $user?->title == 'MS' ? 'checked' : '' }} />
                  <span class="ml-2">Ms</span>
               </label>
            </div>
         </div>
      </div>
      <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userFullName">Full name</label>
            <input type="text" name="userFullName" id="userFullName" class="form-control" placeholder="Enter your full name" aria-describedby="helpId" value="{{ $user->name ?? '' }}" required>
            <small id="helpId" class="text-muted">e.g. Syed Ali Moiz</small>
         </div>
      </div>
      <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userEmail">Email <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="e.g. name@outlook.com"></i> <small class="text-black-50">( Your ticket details will be sent to this email address. )</small></label>
            <input type="text" name="userEmail" id="userEmail" class="form-control" placeholder="Enter your email" aria-describedby="helpId" value="{{ $user->email ?? '' }}" {{ isset($user) && $user->email ? 'disabled' : 'required' }}>
            <small id="helpId" class="text-muted">e.g. name@outlook.com</small>
         </div>
      </div>
      <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userCity">City</label>
            <select class="form-control" aria-describedby="helpId" id="userCity" name="userCity">
               <optgroup label="Pakistan">
                  <option {{ $user?->city_code == '21' ? 'selected' : '' }} value="21" data-country="Pakistan" data-country-code="PK">Karachi</option>
                  <option {{ $user?->city_code == '22' ? 'selected' : '' }} value="22" data-country="Pakistan" data-country-code="PK">Hyderabad</option>
                  <option {{ $user?->city_code == '24' ? 'selected' : '' }} value="24" data-country="Pakistan" data-country-code="PK">Sukkur</option>
                  <option {{ $user?->city_code == '25' ? 'selected' : '' }} value="25" data-country="Pakistan" data-country-code="PK">Larkana</option>
                  <option {{ $user?->city_code == '41' ? 'selected' : '' }} value="41" data-country="Pakistan" data-country-code="PK">Multan</option>
                  <option {{ $user?->city_code == '42' ? 'selected' : '' }} value="42" data-country="Pakistan" data-country-code="PK">Lahore</option>
                  <option {{ $user?->city_code == '43' ? 'selected' : '' }} value="43" data-country="Pakistan" data-country-code="PK">Bahawalpur</option>
                  <option {{ $user?->city_code == '44' ? 'selected' : '' }} value="44" data-country="Pakistan" data-country-code="PK">Gujranwala</option>
                  <option {{ $user?->city_code == '45' ? 'selected' : '' }} value="45" data-country="Pakistan" data-country-code="PK">Sialkot</option>
                  <option {{ $user?->city_code == '46' ? 'selected' : '' }} value="46" data-country="Pakistan" data-country-code="PK">Faisalabad</option>
                  <option {{ $user?->city_code == '51' ? 'selected' : '' }} value="51" data-country="Pakistan" data-country-code="PK">Islamabad/Rawalpindi</option>
                  <option {{ $user?->city_code == '52' ? 'selected' : '' }} value="52" data-country="Pakistan" data-country-code="PK">Gujrat</option>
                  <option {{ $user?->city_code == '53' ? 'selected' : '' }} value="53" data-country="Pakistan" data-country-code="PK">Jhelum</option>
                  <option {{ $user?->city_code == '54' ? 'selected' : '' }} value="54" data-country="Pakistan" data-country-code="PK">Sargodha</option>
                  <option {{ $user?->city_code == '55' ? 'selected' : '' }} value="55" data-country="Pakistan" data-country-code="PK">Mianwali</option>
                  <option {{ $user?->city_code == '61' ? 'selected' : '' }} value="61" data-country="Pakistan" data-country-code="PK">Peshawar</option>
                  <option {{ $user?->city_code == '62' ? 'selected' : '' }} value="62" data-country="Pakistan" data-country-code="PK">Mardan</option>
                  <option {{ $user?->city_code == '63' ? 'selected' : '' }} value="63" data-country="Pakistan" data-country-code="PK">Swat</option>
                  <option {{ $user?->city_code == '64' ? 'selected' : '' }} value="64" data-country="Pakistan" data-country-code="PK">Abbottabad</option>
                  <option {{ $user?->city_code == '65' ? 'selected' : '' }} value="65" data-country="Pakistan" data-country-code="PK">Bannu</option>
                  <option {{ $user?->city_code == '71' ? 'selected' : '' }} value="71" data-country="Pakistan" data-country-code="PK">Quetta</option>
                  <option {{ $user?->city_code == '72' ? 'selected' : '' }} value="72" data-country="Pakistan" data-country-code="PK">Khuzdar</option>
                  <option {{ $user?->city_code == '74' ? 'selected' : '' }} value="74" data-country="Pakistan" data-country-code="PK">Gwadar</option>
                  <option {{ $user?->city_code == '75' ? 'selected' : '' }} value="75" data-country="Pakistan" data-country-code="PK">Sibi</option>
                  <option {{ $user?->city_code == '81' ? 'selected' : '' }} value="81" data-country="Pakistan" data-country-code="PK">Dera Ghazi Khan</option>
                  <option {{ $user?->city_code == '82' ? 'selected' : '' }} value="82" data-country="Pakistan" data-country-code="PK">Rahim Yar Khan</option>
                  <option {{ $user?->city_code == '83' ? 'selected' : '' }} value="83" data-country="Pakistan" data-country-code="PK">DG Khan</option>
                  <option {{ $user?->city_code == '91' ? 'selected' : '' }} value="91" data-country="Pakistan" data-country-code="PK">Gilgit</option>
                  <option {{ $user?->city_code == '92' ? 'selected' : '' }} value="92" data-country="Pakistan" data-country-code="PK">Skardu</option>
                  <option {{ $user?->city_code == '93' ? 'selected' : '' }} value="93" data-country="Pakistan" data-country-code="PK">Chitral</option>
                  <option {{ $user?->city_code == '94' ? 'selected' : '' }} value="94" data-country="Pakistan" data-country-code="PK">Hunza</option>
                  <option {{ $user?->city_code == '95' ? 'selected' : '' }} value="95" data-country="Pakistan" data-country-code="PK">Muzaffarabad</option>
                  <option {{ $user?->city_code == '96' ? 'selected' : '' }} value="96" data-country="Pakistan" data-country-code="PK">Mirpur</option>
               </optgroup>

               <optgroup label="India">
                  <option {{ $user?->city_code == '101' ? 'selected' : '' }} value="101" data-country="India" data-country-code="IN">New Delhi</option>
                  <option {{ $user?->city_code == '102' ? 'selected' : '' }} value="102" data-country="India" data-country-code="IN">Mumbai</option>
                  <option {{ $user?->city_code == '103' ? 'selected' : '' }} value="103" data-country="India" data-country-code="IN">Kolkata</option>
                  <option {{ $user?->city_code == '104' ? 'selected' : '' }} value="104" data-country="India" data-country-code="IN">Chennai</option>
                  <option {{ $user?->city_code == '105' ? 'selected' : '' }} value="105" data-country="India" data-country-code="IN">Pune</option>
                  <option {{ $user?->city_code == '106' ? 'selected' : '' }} value="106" data-country="India" data-country-code="IN">Hyderabad</option>
                  <option {{ $user?->city_code == '107' ? 'selected' : '' }} value="107" data-country="India" data-country-code="IN">Ahmedabad</option>
                  <option {{ $user?->city_code == '108' ? 'selected' : '' }} value="108" data-country="India" data-country-code="IN">Bengaluru</option>
               </optgroup>

               <optgroup label="China">
                  <option {{ $user?->city_code == '201' ? 'selected' : '' }} value="201" data-country="China" data-country-code="CN">Shanghai</option>
                  <option {{ $user?->city_code == '202' ? 'selected' : '' }} value="202" data-country="China" data-country-code="CN">Tianjin</option>
                  <option {{ $user?->city_code == '203' ? 'selected' : '' }} value="203" data-country="China" data-country-code="CN">Chongqing</option>
                  <option {{ $user?->city_code == '204' ? 'selected' : '' }} value="204" data-country="China" data-country-code="CN">Shenyang</option>
                  <option {{ $user?->city_code == '205' ? 'selected' : '' }} value="205" data-country="China" data-country-code="CN">Nanjing</option>
                  <option {{ $user?->city_code == '206' ? 'selected' : '' }} value="206" data-country="China" data-country-code="CN">Wuhan</option>
                  <option {{ $user?->city_code == '207' ? 'selected' : '' }} value="207" data-country="China" data-country-code="CN">Chengdu</option>
                  <option {{ $user?->city_code == '208' ? 'selected' : '' }} value="208" data-country="China" data-country-code="CN">Xi’an</option>
                  <option {{ $user?->city_code == '209' ? 'selected' : '' }} value="209" data-country="China" data-country-code="CN">Guangzhou</option>
               </optgroup>

               <optgroup label="Egypt">
                  <option {{ $user?->city_code == '301' ? 'selected' : '' }} value="301" data-country="Egypt" data-country-code="EG">Cairo/Giza/Qalyubia</option>
                  <option {{ $user?->city_code == '302' ? 'selected' : '' }} value="302" data-country="Egypt" data-country-code="EG">Alexandria</option>
                  <option {{ $user?->city_code == '303' ? 'selected' : '' }} value="303" data-country="Egypt" data-country-code="EG">Arish</option>
                  <option {{ $user?->city_code == '304' ? 'selected' : '' }} value="304" data-country="Egypt" data-country-code="EG">Asyut</option>
               </optgroup>

               <optgroup label="United States">
                  <option {{ $user?->city_code == '401' ? 'selected' : '' }} value="401" data-country="United States" data-country-code="US">Washington DC</option>
                  <option {{ $user?->city_code == '402' ? 'selected' : '' }} value="402" data-country="United States" data-country-code="US">New York City (Manhattan)</option>
                  <option {{ $user?->city_code == '403' ? 'selected' : '' }} value="403" data-country="United States" data-country-code="US">Los Angeles</option>
                  <option {{ $user?->city_code == '404' ? 'selected' : '' }} value="404" data-country="United States" data-country-code="US">Miami (Florida)</option>
               </optgroup>

               <optgroup label="Europe">
                  <option {{ $user?->city_code == '501' ? 'selected' : '' }} value="501" data-country="Greece" data-country-code="GR">Athens</option>
                  <option {{ $user?->city_code == '502' ? 'selected' : '' }} value="502" data-country="Netherlands" data-country-code="NL">Amsterdam</option>
                  <option {{ $user?->city_code == '503' ? 'selected' : '' }} value="503" data-country="Belgium" data-country-code="BE">Brussels</option>
                  <option {{ $user?->city_code == '504' ? 'selected' : '' }} value="504" data-country="France" data-country-code="FR">Paris</option>
                  <option {{ $user?->city_code == '505' ? 'selected' : '' }} value="505" data-country="Spain" data-country-code="ES">Madrid</option>
                  <option {{ $user?->city_code == '506' ? 'selected' : '' }} value="506" data-country="Italy" data-country-code="IT">Rome</option>
                  <option {{ $user?->city_code == '507' ? 'selected' : '' }} value="507" data-country="Germany" data-country-code="DE">Berlin</option>
                  <option {{ $user?->city_code == '508' ? 'selected' : '' }} value="508" data-country="United Kingdom" data-country-code="GB">London</option>
               </optgroup>

               <optgroup label="Australia & New Zealand">
                  <option {{ $user?->city_code == '601' ? 'selected' : '' }} value="601" data-country="Australia" data-country-code="AU">Canberra/Sydney</option>
                  <option {{ $user?->city_code == '602' ? 'selected' : '' }} value="602" data-country="Australia" data-country-code="AU">Brisbane/Gold Coast</option>
                  <option {{ $user?->city_code == '603' ? 'selected' : '' }} value="603" data-country="New Zealand" data-country-code="NZ">Auckland</option>
               </optgroup>

               <optgroup label="Others">
                  <option {{ $user?->city_code == '701' ? 'selected' : '' }} value="701" data-country="Brazil" data-country-code="BR">São Paulo</option>
                  <option {{ $user?->city_code == '702' ? 'selected' : '' }} value="702" data-country="Canada" data-country-code="CA">Toronto</option>
                  <option {{ $user?->city_code == '703' ? 'selected' : '' }} value="703" data-country="Canada" data-country-code="CA">Vancouver</option>
                  <option {{ $user?->city_code == '704' ? 'selected' : '' }} value="704" data-country="South Africa" data-country-code="ZA">Cape Town</option>
               </optgroup>
            </select>
         </div>
      </div>
      {{-- <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userCity">City</label>
            <input type="text" name="userCity" id="userCity" class="form-control" required placeholder="Enter your City" aria-describedby="helpId" value="{{ $user->city ?? '' }}">
            <small id="helpId" class="text-muted">e.g. Karachi</small>
         </div>
      </div> --}}
      <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userPhoneCode">Phone Code</label>
            <input type="text" max="4" name="userPhoneCode" id="userPhoneCode" class="form-control" required placeholder="Enter your phone code" maxlength="5" aria-describedby="helpId" value="{{ $user->phone_code ?? '' }}">
            <small id="helpId" class="text-muted">e.g. 92</small>
         </div>
      </div>
      <div class="col-md-12 col-lg-6">
         <div class="form-group">
            <label for="userPhone">Phone Number</label>
            <input type="number" name="userPhone" id="userPhone" class="form-control" required placeholder="Enter your phone number" aria-describedby="helpId" value="{{ $user->phone ?? '' }}">
            <small id="helpId" class="text-muted">e.g. 3320234557</small>
         </div>
      </div>
   </div>
   <div class="form-check cont-check">
      <input class="form-check-input" type="checkbox" id="acceptOffers" {{ isset($user) && $user->accept_notification ? 'checked' : '' }} >
      <label class="form-check-label" for="acceptOffers">
         <p>I agree to receive travel related information and deals</p>
      </label>
   </div>
</div>
{{-- @dd($flightData) --}}
@if (!empty($flightData['passengerTypes']))
   @foreach ($flightData['passengerTypes'] as $key => $type)
      @if (!empty($flightData['paxCount'][$key]))
         <div class=" paxDetails">
               @for ($i = 1; $i <= $flightData['paxCount'][$key]; $i++)
                  <div class="contact contact2">
                     <h2>Traveler Details for {{ $type }} {{ $i }}</h2>
                     <input type="hidden" name="{{ $key }}_type[]" value="{{ $type }}">
                     <input type="hidden" name="{{ $key }}_id[]">
                     <div class="row">   
                        <div class="col-lg-6">
                           <div class="form-group">
                              <label for="savedTravelor_{{ $key }}_{{ $i }}">Saved Travelers</label>
                              <select id="savedTravelor_{{ $key }}_{{ $i }}" class="form-control saved-traveler" {{ $user ? '' : 'disabled' }} aria-describedby="helpId">
                                 <option >+ Add a new traveler</option>
                                 @if ($user)
                                    @foreach ($user->passengers as $passenger)
                                       <option 
                                          data-id="{{ $passenger->id }}"
                                          data-given_name="{{ $passenger->given_name }}"
                                          data-surname="{{ $passenger->surname }}"
                                          data-dob="{{ \Illuminate\Support\Carbon::parse($passenger->dob)->format('Y-m-d') }}"
                                          data-nationality="{{ $passenger->nationality }}"
                                          data-passport_number="{{ $passenger->passport_no }}"
                                          data-passport_expiry="{{ \Illuminate\Support\Carbon::parse($passenger->passport_expiry)->format('Y-m-d') }}"
                                          data-title="{{ $passenger->title }}"
                                       >
                                          {{ $passenger->given_name }} {{ $passenger->surname }}
                                       </option>
                                    @endforeach
                                 @endif
                              </select>
                              @if ($user)
                                 <small id="helpId" class="text-muted">Select a passenger to automatically populate their details.</small>
                              @else
                                 <small id="helpId" class="text-muted"><a href="{{ route('login') }}">Sign in</a> to view your saved travelers lists.</small>
                              @endif
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group">
                              <label for="gendertitle_{{ $i }}">Select Title</label>
                              <div class="inline-flex">
                                 <label for="title_mr_{{ $key }}_{{ $i }}" class="borlook d-flex p-3">
                                    <input class="hidden-radio" type="radio" id="title_mr_{{ $key }}_{{ $i }}" value="Mr" name="title_{{ $key }}_{{ $i }}" checked/>
                                    <span class="ml-2">Mr</span>
                                 </label>
                                 <label for="title_mrs_{{ $key }}_{{ $i }}" class="borlook d-flex p-3">
                                    <input class="hidden-radio" type="radio" id="title_mrs_{{ $key }}_{{ $i }}" value="Mrs" name="title_{{ $key }}_{{ $i }}" />
                                    <span class="ml-2">Mrs</span>
                                 </label>
                                 <label for="title_ms_{{ $key }}_{{ $i }}" class="borlook d-flex p-3">
                                    <input class="hidden-radio" type="radio" id="title_ms_{{ $key }}_{{ $i }}" value="Ms" name="title_{{ $key }}_{{ $i }}" />
                                    <span class="ml-2">Ms</span>
                                 </label>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                           <label for="{{ $i }}_name">Given Name</label>
                           <div class="infos">
                              <input type="text" class="form-control form-control-info" aria-describedby="helpId" name="{{ $key }}_name[]" id="{{ $i }}_name" required>
                              <div class="tooltip-container">
                                 <i class="fa-solid fa-circle-info"></i>
                                 <div class="tooltip-content">
                                    <h2>Given Name</h2>
                                    <p>Enter as highlighted</p>
                                    @if ($flightData['isLocal'])
                                       <img src="{{ asset('assets/images/passenger/cnic-name.png') }}" alt="Tooltip Image">
                                    @else
                                       <img src="{{ asset('assets/images/passenger/passport-vctor2.jpg') }}" alt="Tooltip Image">
                                    @endif
                                 </div>
                              </div>
                           </div>
                           <small id="helpId" class="text-muted">Enter given name as per passport to avoid boarding issues.</small>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                           <label for="{{ $i }}_surname">Surname</label>
                              <div class="infos">
                                 <input type="text" name="{{ $key }}_surname[]" id="{{ $i }}_surname" class="form-control form-control-info" aria-describedby="helpId" required>
                                 {{-- <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Surname"></i> --}}
                                 <div class="tooltip-container">
                                    <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Surname"></i>
                                    <div class="tooltip-content">
                                       <h2>Surname</h2>
                                       <p>Enter as highlighted</p>
                                    @if ($flightData['isLocal'])
                                       <img src="{{ asset('assets/images/passenger/cnic-name.png') }}" alt="Tooltip Image">
                                    @else
                                       <img src="{{ asset('assets/images/passenger/passport-vctor.jpg') }}" alt="Tooltip Image">
                                    @endif
                                    </div>
                                 </div>
                              </div>
                              <small id="helpId" class="text-muted">Enter surname as per passport to avoid boarding issues.</small>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_dob">Date of Birth</label>
                              <input class="form-control" id="{{ $i }}_dob" type="date" name="{{ $key }}_dob[]" required>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_nationality">Nationality</label>
                              <select class="form-control" aria-describedby="helpId" id="{{ $i }}_nationality" name="{{ $key }}_nationality[]">
                                 {{-- data-code = country code --}}
                                 <option disabled>Select one</option>
                                 <option selected value="PK" data-code="+92">Pakistani</option>
                                 <option value="AF" data-code="+93">Afghan</option>
                                 <option value="AL" data-code="+355">Albanian</option>
                                 <option value="DZ" data-code="+213">Algerian</option>
                                 <option value="US" data-code="+1">American</option>
                                 <option value="AD" data-code="+376">Andorran</option>
                                 <option value="AO" data-code="+244">Angolan</option>
                                 <option value="AG" data-code="+1-268">Antiguans</option>
                                 <option value="AR" data-code="+54">Argentinean</option>
                                 <option value="AM" data-code="+374">Armenian</option>
                                 <option value="AU" data-code="+61">Australian</option>
                                 <option value="AT" data-code="+43">Austrian</option>
                                 <option value="AZ" data-code="+994">Azerbaijani</option>
                                 <option value="BS" data-code="+1-242">Bahamian</option>
                                 <option value="BH" data-code="+973">Bahraini</option>
                                 <option value="BD" data-code="+880">Bangladeshi</option>
                                 <option value="BB" data-code="+1-246">Barbadian</option>
                                 <option value="BW" data-code="+267">Batswana</option>
                                 <option value="BY" data-code="+375">Belarusian</option>
                                 <option value="BE" data-code="+32">Belgian</option>
                                 <option value="BZ" data-code="+501">Belizean</option>
                                 <option value="BJ" data-code="+229">Beninese</option>
                                 <option value="BT" data-code="+975">Bhutanese</option>
                                 <option value="BO" data-code="+591">Bolivian</option>
                                 <option value="BA" data-code="+387">Bosnian</option>
                                 <option value="BR" data-code="+55">Brazilian</option>
                                 <option value="GB" data-code="+44">British</option>
                                 <option value="BN" data-code="+673">Bruneian</option>
                                 <option value="BG" data-code="+359">Bulgarian</option>
                                 <option value="BF" data-code="+226">Burkinabe</option>
                                 <option value="MM" data-code="+95">Burmese</option>
                                 <option value="BI" data-code="+257">Burundian</option>
                                 <option value="KH" data-code="+855">Cambodian</option>
                                 <option value="CM" data-code="+237">Cameroonian</option>
                                 <option value="CA" data-code="+1">Canadian</option>
                                 <option value="CV" data-code="+238">Cape Verdean</option>
                                 <option value="CF" data-code="+236">Central African</option>
                                 <option value="TD" data-code="+235">Chadian</option>
                                 <option value="CL" data-code="+56">Chilean</option>
                                 <option value="CN" data-code="+86">Chinese</option>
                                 <option value="CO" data-code="+57">Colombian</option>
                                 <option value="KM" data-code="+269">Comoran</option>
                                 <option value="CG" data-code="+242">Congolese</option>
                                 <option value="CR" data-code="+506">Costa Rican</option>
                                 <option value="HR" data-code="+385">Croatian</option>
                                 <option value="CU" data-code="+53">Cuban</option>
                                 <option value="CY" data-code="+357">Cypriot</option>
                                 <option value="CZ" data-code="+420">Czech</option>
                                 <option value="DK" data-code="+45">Danish</option>
                                 <option value="DJ" data-code="+253">Djiboutian</option>
                                 <option value="DO" data-code="+1-809">Dominican</option>
                                 <option value="NL" data-code="+31">Dutch</option>
                                 <option value="EC" data-code="+593">Ecuadorean</option>
                                 <option value="EG" data-code="+20">Egyptian</option>
                                 <option value="AE" data-code="+971">Emirati</option>
                                 <option value="ER" data-code="+291">Eritrean</option>
                                 <option value="EE" data-code="+372">Estonian</option>
                                 <option value="ET" data-code="+251">Ethiopian</option>
                                 <option value="FJ" data-code="+679">Fijian</option>
                                 <option value="FI" data-code="+358">Finnish</option>
                                 <option value="FR" data-code="+33">French</option>
                                 <option value="DE" data-code="+49">German</option>
                                 <option value="GH" data-code="+233">Ghanaian</option>
                                 <option value="GR" data-code="+30">Greek</option>
                                 <option value="GT" data-code="+502">Guatemalan</option>
                                 <option value="HT" data-code="+509">Haitian</option>
                                 <option value="HN" data-code="+504">Honduran</option>
                                 <option value="HU" data-code="+36">Hungarian</option>
                                 <option value="IS" data-code="+354">Icelander</option>
                                 <option value="IN" data-code="+91">Indian</option>
                                 <option value="ID" data-code="+62">Indonesian</option>
                                 <option value="IR" data-code="+98">Iranian</option>
                                 <option value="IQ" data-code="+964">Iraqi</option>
                                 <option value="IE" data-code="+353">Irish</option>
                                 <option value="IL" data-code="+972">Israeli</option>
                                 <option value="IT" data-code="+39">Italian</option>
                                 <option value="JM" data-code="+1-876">Jamaican</option>
                                 <option value="JP" data-code="+81">Japanese</option>
                                 <option value="JO" data-code="+962">Jordanian</option>
                                 <option value="KZ" data-code="+7">Kazakhstani</option>
                                 <option value="KE" data-code="+254">Kenyan</option>
                                 <option value="KW" data-code="+965">Kuwaiti</option>
                                 <option value="KG" data-code="+996">Kyrgyz</option>
                                 <option value="LA" data-code="+856">Laotian</option>
                                 <option value="LV" data-code="+371">Latvian</option>
                                 <option value="LB" data-code="+961">Lebanese</option>
                                 <option value="LR" data-code="+231">Liberian</option>
                                 <option value="LY" data-code="+218">Libyan</option>
                                 <option value="LT" data-code="+370">Lithuanian</option>
                                 <option value="LU" data-code="+352">Luxembourger</option>
                                 <option value="MY" data-code="+60">Malaysian</option>
                                 <option value="MV" data-code="+960">Maldivian</option>
                                 <option value="ML" data-code="+223">Malian</option>
                                 <option value="MT" data-code="+356">Maltese</option>
                                 <option value="MX" data-code="+52">Mexican</option>
                                 <option value="MA" data-code="+212">Moroccan</option>
                                 <option value="NP" data-code="+977">Nepalese</option>
                                 <option value="NZ" data-code="+64">New Zealander</option>
                                 <option value="NG" data-code="+234">Nigerian</option>
                                 <option value="NO" data-code="+47">Norwegian</option>
                                 <option value="OM" data-code="+968">Omani</option>
                                 <option value="PK" data-code="+92">Pakistani</option>
                                 <option value="PH" data-code="+63">Filipino</option>
                              </select>
                           </div>
                        </div>
                     </div>
                   @if ($key !== 'inf')
                     <div class="row">
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                           <label for="{{ $i }}_passportnumber">@if ($flightData['isLocal']) Cnic @else Passport @endif Number</label>
                              <div class="infos">
                                 <input type="text" name="{{ $key }}_passportnumber[]" id="{{ $i }}_passportnumber" class="form-control" aria-describedby="helpId" required>
                                    <div class="tooltip-container">
                                       <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Passport Number"></i>
                                       <div class="tooltip-content">
                                          <h2>@if ($flightData['isLocal']) Cnic @else Passport @endif Number</h2>
                                          <p>Enter as highlighted</p>
                                          @if ($flightData['isLocal'])
                                             <img src="{{ asset('assets/images/passenger/cnic-no.png') }}" alt="Tooltip Image">
                                          @else
                                             <img src="{{ asset('assets/images/passenger/passport-vctor.jpg') }}" alt="Tooltip Image">
                                          @endif
                                       </div>
                                    </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_passportexp">@if ($flightData['isLocal']) Cnic @else Passport @endif Expiry</label>
                              <input class="form-control" id="{{ $i }}_passportexp" type="date" name="{{ $key }}_passportexp[]" required value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}">
                              <small id="helpId" class="text-muted">Please ensure is currently valid</small>
                           </div>
                        </div>
                     </div>
                   @endif
                </div>
             @endfor
         </div>
      @endif
   @endforeach
@endif
<script>
   $(document).ready(function () {
      function updateDisabledPassengers() {
         const usedPassengerIds = [];

         $('.contact2').each(function () {
            const val = $(this).find('input[name$="_id[]"]').val();
            if (val) {
               usedPassengerIds.push(val);
            }
         });

         $('.saved-traveler').each(function () {
            const $select = $(this);
            const currentVal = $select.val();

            $select.find('option').each(function () {
               const $option = $(this);
               const id = $option.data('id');

               if (!id) return; // Skip "+ Add a new traveler"
               
               // Disable only if this passenger is used in another traveler
               if (usedPassengerIds.includes(String(id)) && $option.val() !== currentVal) {
                  $option.prop('disabled', true);
               } else {
                  $option.prop('disabled', false);
               }
            });
         });
      }

      $('.saved-traveler').on('change', function () {
         const $select = $(this);
         const $selected = $select.find('option:selected');

         const $container = $select.closest('.contact2');

         // Clear fields first
         $container.find('input[name$="_id[]"]').val('');
         $container.find('input[name$="_name[]"]').val('');
         $container.find('input[name$="_surname[]"]').val('');
         $container.find('input[name$="_dob[]"]').val('');
         $container.find('select[name$="_nationality[]"]').val('PK');
         $container.find('input[name$="_passportnumber[]"]').val('');
         $container.find('input[name$="_passportexp[]"]').val('');
         $container.find('input[type="radio"][value="Mr"]').prop('checked', true);

         // If "+ Add a new traveler" selected, just clear and update disables
         if (!$selected.data('id')) {
            updateDisabledPassengers();
            return;
         }

         const passenger = {
            id: $selected.data('id'),
            given_name: $selected.data('given_name'),
            surname: $selected.data('surname'),
            dob: $selected.data('dob'),
            nationality: $selected.data('nationality'),
            passport_number: $selected.data('passport_number'),
            passport_expiry: $selected.data('passport_expiry'),
            title: $selected.data('title')
         };

         $container.find('input[name$="_id[]"]').val(passenger.id);
         $container.find('input[name$="_name[]"]').val(passenger.given_name);
         $container.find('input[name$="_surname[]"]').val(passenger.surname);
         $container.find('input[name$="_dob[]"]').val(passenger.dob);
         $container.find('select[name$="_nationality[]"]').val(passenger.nationality);
         $container.find('input[name$="_passportnumber[]"]').val(passenger.passport_number);
         $container.find('input[name$="_passportexp[]"]').val(passenger.passport_expiry);
         $container.find(`input[type="radio"][value="${passenger.title}"]`).prop('checked', true);

         updateDisabledPassengers();
      });
   });
</script>

