<div class="contact">
    <h2>Contact Details</h2>
    <div class="row">
       <div class="col-md-12 col-lg-6">
          <div class="form-group">
             <label for="userFullName">Full name</label>
             <input type="text" name="userFullName" id="userFullName" class="form-control" required placeholder="Enter your full name" aria-describedby="helpId" value="Syed Ali Moiz">
             <small id="helpId" class="text-muted">e.g. Syed Ali Moiz</small>
          </div>
       </div>
       <div class="col-md-12 col-lg-6">
          <div class="form-group">
             <label for="userEmail">Email <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="e.g. name@outlook.com"></i> <small class="text-black-50">( Your ticket details will be sent to this email address. )</small></label>
             <input type="text" name="userEmail" required id="userEmail" class="form-control" placeholder="Enter your email" aria-describedby="helpId" value="name@outlook.com">
             <small id="helpId" class="text-muted">e.g. name@outlook.com</small>
          </div>
       </div>
       <div class="col-md-12 col-lg-6">
          <div class="form-group">
             <label for="userPhoneCode">Phone Code</label>
             <input type="text" name="userPhoneCode" id="userPhoneCode" class="form-control" required placeholder="Enter your phone code" maxlength="5" aria-describedby="helpId" value="92">
             <small id="helpId" class="text-muted">e.g. 92</small>
          </div>
       </div>
       <div class="col-md-12 col-lg-6">
          <div class="form-group">
             <label for="userPhone">Phone Number</label>
             <input type="text" name="userPhone" id="userPhone" class="form-control" required placeholder="Enter your phone number" aria-describedby="helpId" value="3320234557">
             <small id="helpId" class="text-muted">e.g. 3320234557</small>
          </div>
       </div>
    </div>           
    <div class="form-check cont-check">
       <input class="form-check-input" type="checkbox" id="acceptOffers" checked>
       <label class="form-check-label" for="acceptOffers">
             <p>I agree to receive travel related information and deals</p>
       </label>
    </div>
 </div>
@if (isset($flightData['passengerTypes']) && !empty($flightData['passengerTypes']))
   @foreach ($flightData['passengerTypes'] as $key => $type)
      @if(isset($flightData['paxCount'][$key]) && $flightData['paxCount'][$key] > 0)
            <div class=" paxDetails">
               @for ($i = 1; $i <= $flightData['paxCount'][$key]; $i++)
                  <div class="contact contact2">
                     <h2>Traveler Details for {{ $type }} {{ $i }}</h2>
                     <input type="hidden" name="{{ $key }}_type[]" value="{{ $type }}">
                     <div class="row">   
                        {{-- <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="">Saved Travelers</label>
                              <select class="form-control" disabled  aria-describedby="helpId">
                                 <option value="">+ Add a new traveler</option>
                              </select>
                              <br>
                              <label for=""><a href="#">Sign in</a> to view your Saved Travelers List.S</label>
                           </div>
                        </div> --}}
                        <div class="col-md-12 col-lg-6">
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
                              <input type="text" class="form-control form-control-info" aria-describedby="helpId" name="{{ $key }}_name[]" id="{{ $i }}_name" value="Alii_{{ $key }}{{ $i }}" required>
                              <div class="tooltip-container">
                                 <i class="fa-solid fa-circle-info"></i>
                                 <div class="tooltip-content">
                                    <h2>Given Name</h2>
                                    <p>Enter as highlighted in passport</p>
                                    <img src="/assets/images/passport-vctor.jpg" alt="Tooltip Image">
                                 </div>
                              </div>
                           </div>
                           <small id="helpId" class="text-muted">Enter name as per Passport to avoid boarding issues.</small>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                           <label for="{{ $i }}_surname">Surname</label>
                              <div class="infos">
                                 <input type="text" name="{{ $key }}_surname[]" id="{{ $i }}_surname" class="form-control form-control-info" aria-describedby="helpId" value="Syed_{{ $key }}{{ $i }}" required>
                                 <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Surname"></i>
                              </div>
                              <small id="helpId" class="text-muted">Enter name as per Passport to avoid boarding issues.</small>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_dob">Date of Birth</label>
                              <input class="form-control" id="{{ $i }}_dob" type="date" name="{{ $key }}_dob[]" value="{{ $key == 'adt' ? '2002-07-10' : ($key == 'chd' ? '2020-03-10' : '2025-03-10') }}" required>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 col-lg-12">
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
                     <div class="row">
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                           <label for="{{ $i }}_passportnumber">Passport Number</label>
                              <div class="infos">
                                 <input type="text" name="{{ $key }}_passportnumber[]" id="{{ $i }}_passportnumber" class="form-control" aria-describedby="helpId" value="asd252466As1" required>
                                 <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Passport Number"></i>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_passportexp">Passport Expiry</label>
                              <input class="form-control" id="{{ $i }}_passportexp" type="date" name="{{ $key }}_passportexp[]" value="2026-07-10" required>
                              <small id="helpId" class="text-muted">Please ensure is currently valid</small>
                           </div>
                        </div>
                     </div>
                     {{-- <div class="row">
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_area_code">Area Code</label>
                              <div class="infos">
                                 <input type="number" name="{{ $key }}_area_code[]" id="{{ $i }}_area_code" class="form-control" value="123123" aria-describedby="helpId" required>
                                 <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Area Code"></i>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_phone_code">Enter Country Phone Code</label>
                              <div class="infos">
                                 <input type="number" name="{{ $key }}_phone_code[]" id="{{ $i }}_phone_code" class="form-control" value="123123" aria-describedby="helpId" required>
                                 <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Enter Country Phone Code"></i>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="{{ $i }}_phone">Enter Phone</label>
                              <div class="infos">
                                 <input type="number" name="{{ $key }}_phone[]" id="{{ $i }}_phone" class="form-control" value="123123" aria-describedby="helpId" required>
                                 <i class="fa-solid fa-circle-info" data-toggle="tooltip" data-placement="right" title="Enter Phone"></i>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-12 col-lg-6">
                           <div class="form-group">
                              <label for="">Frequent Flyer Number <span>(optional)</span></label>
                              <input type="number" name="" id="" class="form-control" placeholder="" aria-describedby="helpId">
                              <small id="helpId" class="text-muted">Loyalty points / miles won't be added for incorrect entries.</small>
                           </div>
                        </div>
                     </div> --}}
                  </div>
               @endfor
            </div>
      @endif
   @endforeach    
@endif
