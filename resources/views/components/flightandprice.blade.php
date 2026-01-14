<div class="bokkings-bar">
   <div class="book-head">
      <div class="youbook">
         <h2>Your Bookings</h2>
      </div>
      <div class="depar-head">
         <ul>
            <li>
               <p>Departing</p>
            </li>
            <li>
               <p><i class="fa-regular fa-calendar"></i> {{$flightData['departureFlight']['departureDate'] ?? '--'}}</p>
            </li>
         </ul>
      </div>
   </div>
   <div class="book-flex">
      <div class="emr w-25">
         <img src="/assets/images/Fly_Jinnah_logo.jpg" alt="Fly Jinnah logo">
      </div>
   </div>
   <div class="d-flex flex-column">
      @if (!empty($flightData['departureFlight']))
         <div class="der-time der-time3 mb-2">
            <ul>
               <li><h2>{{$flightData['departureFlight']['departureTime']}}</h2></li>
               <li>
                  <div class="stays">
                     <p>{{$flightData['departureFlight']['timeDifference']}}</p>
                  </div>
               </li>
               <li><div class="tims"><h2>{{$flightData['departureFlight']['arrivalTime']}}</h2></div></li>
            </ul>
            <div class="citys citys2">
               <div class="cit">
                  <ul>
                     <li><p>{{$flightData['departureFlight']['originCode']}}</p></li>
                     <li><p>{{ $flightData['departureFlight']['isConnected'] ? '1 Stop' : 'Nonstop' }}</p></li>
                     <li><p>{{$flightData['departureFlight']['destinationCode']}}</p></li>
                  </ul>
               </div>
            </div>
         </div>
      @endif
      @if (!empty($flightData['returnFlight']))
         <div class="der-time der-time3 mb-2">
            <ul>
               <li>
                  <h2>{{$flightData['returnFlight']['departureTime']}}</h2>
               </li>
               <li>
                  <div class="stays">
                     <p>
                        {{$flightData['returnFlight']['timeDifference']}}
                     </p>
                  </div>
               </li>
               <li><div class="tims"><h2>{{$flightData['returnFlight']['arrivalTime']}}</h2></div></li>
            </ul>
            <div class="citys citys2">
               <div class="cit">
                  <ul>
                     <li>
                        <p>{{$flightData['returnFlight']['originCode']}}</p>
                     </li>
                     <li>
                        <p>{{ $flightData['returnFlight']['isConnected'] ? '1 Stop' : 'Nonstop' }}</p>
                     </li>
                     <li>
                        <p>{{$flightData['returnFlight']['destinationCode']}}</p>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      @endif
   </div>
</div>